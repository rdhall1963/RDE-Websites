/**
 * CCBill Payment Gateway Configuration
 */

// Environment configuration
const CCBILL_CONFIG = {
    // Production settings
    production: {
        baseUrl: 'https://bill.ccbill.com',
        signupUrl: 'https://bill.ccbill.com/jpost/signup.cgi',
        webhookUrl: 'https://your-domain.com/wp-json/ccbill/v1/webhook',
    },
    
    // Sandbox/Test settings
    sandbox: {
        baseUrl: 'https://sandbox.ccbill.com',
        signupUrl: 'https://sandbox.ccbill.com/jpost/signup.cgi',
        webhookUrl: 'https://your-domain.com/wp-json/ccbill/v1/webhook',
    },
    
    // Account credentials (to be replaced with actual values)
    account: {
        accountNumber: 'YOUR_ACCOUNT_NUMBER',
        subAccount: 'YOUR_SUBACCOUNT',
        formName: 'YOUR_FORM_NAME',
        salt: 'YOUR_SALT_KEY',
        currencyCode: '840', // USD
    },
    
    // Subscription settings
    subscription: {
        recurringPricingEnabled: true,
        defaultPeriod: 30, // days
        flexFormUrl: 'https://bill.ccbill.com/jpost/signup.cgi',
    }
};

/**
 * CCBill API Client
 */
class CCBillClient {
    constructor(config, testMode = false) {
        this.config = config;
        this.testMode = testMode;
        this.baseUrl = testMode ? config.sandbox.baseUrl : config.production.baseUrl;
    }
    
    /**
     * Generate payment form URL
     */
    generatePaymentUrl(params) {
        const {
            price,
            period = 30,
            customerId,
            customerEmail,
            orderId,
            firstName,
            lastName,
            ...additionalParams
        } = params;
        
        const baseParams = {
            clientAccnum: this.config.account.accountNumber,
            clientSubacc: this.config.account.subAccount,
            formName: this.config.account.formName,
            currencyCode: this.config.account.currencyCode,
            formPrice: price,
            formPeriod: period,
            customer_fname: firstName,
            customer_lname: lastName,
            email: customerEmail,
            order_id: orderId,
            ...additionalParams
        };
        
        // Generate security hash
        const hashString = `${price}${period}${this.config.account.currencyCode}${this.config.account.salt}`;
        baseParams.formDigest = this.generateMD5Hash(hashString);
        
        const signupUrl = this.testMode 
            ? this.config.sandbox.signupUrl 
            : this.config.production.signupUrl;
        
        return this.buildUrl(signupUrl, baseParams);
    }
    
    /**
     * Build URL with query parameters
     */
    buildUrl(baseUrl, params) {
        const queryString = Object.keys(params)
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
            .join('&');
        
        return `${baseUrl}?${queryString}`;
    }
    
    /**
     * Generate MD5 hash (for browser environments, use crypto-js or similar)
     */
    generateMD5Hash(string) {
        // In a real implementation, use a proper MD5 library
        // This is a placeholder
        return string; // Replace with actual MD5 hash
    }
    
    /**
     * Verify webhook signature
     */
    verifyWebhookSignature(data, signature) {
        const expectedSignature = this.generateWebhookSignature(data);
        return expectedSignature === signature;
    }
    
    /**
     * Generate webhook signature
     */
    generateWebhookSignature(data) {
        const signatureString = `${data.subscriptionId}${data.timestamp}${this.config.account.salt}`;
        return this.generateMD5Hash(signatureString);
    }
    
    /**
     * Process webhook event
     */
    processWebhook(eventData) {
        const { eventType, subscriptionId, orderId, ...metadata } = eventData;
        
        switch (eventType) {
            case 'NewSaleSuccess':
                return this.handleNewSale(subscriptionId, orderId, metadata);
            case 'NewSaleFailure':
                return this.handleSaleFailure(subscriptionId, orderId, metadata);
            case 'Renewal':
                return this.handleRenewal(subscriptionId, orderId, metadata);
            case 'Cancellation':
                return this.handleCancellation(subscriptionId, orderId, metadata);
            case 'Chargeback':
                return this.handleChargeback(subscriptionId, orderId, metadata);
            default:
                console.warn(`Unknown event type: ${eventType}`);
                return false;
        }
    }
    
    handleNewSale(subscriptionId, orderId, metadata) {
        console.log(`New sale: ${subscriptionId} for order ${orderId}`);
        // Implement sale handling logic
        return true;
    }
    
    handleSaleFailure(subscriptionId, orderId, metadata) {
        console.log(`Sale failed: ${subscriptionId} for order ${orderId}`);
        // Implement failure handling logic
        return true;
    }
    
    handleRenewal(subscriptionId, orderId, metadata) {
        console.log(`Renewal: ${subscriptionId} for order ${orderId}`);
        // Implement renewal handling logic
        return true;
    }
    
    handleCancellation(subscriptionId, orderId, metadata) {
        console.log(`Cancellation: ${subscriptionId} for order ${orderId}`);
        // Implement cancellation handling logic
        return true;
    }
    
    handleChargeback(subscriptionId, orderId, metadata) {
        console.log(`Chargeback: ${subscriptionId} for order ${orderId}`);
        // Implement chargeback handling logic
        return true;
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { CCBillClient, CCBILL_CONFIG };
}
