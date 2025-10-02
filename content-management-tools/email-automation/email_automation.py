#!/usr/bin/env python3
"""
Email Automation Setup
Configure SMTP and create email templates for WordPress sites
"""

import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from pathlib import Path
import json

class EmailAutomation:
    def __init__(self, config_file='email_config.json'):
        self.config_file = Path(config_file)
        self.config = self.load_config()
    
    def load_config(self):
        """Load email configuration"""
        if self.config_file.exists():
            with open(self.config_file, 'r') as f:
                return json.load(f)
        return {}
    
    def save_config(self, config):
        """Save email configuration"""
        with open(self.config_file, 'w') as f:
            json.dump(config, f, indent=2)
        self.config = config
    
    def setup_smtp(self, host, port, username, password, use_tls=True):
        """Setup SMTP configuration"""
        config = {
            'smtp_host': host,
            'smtp_port': port,
            'smtp_username': username,
            'smtp_password': password,
            'use_tls': use_tls
        }
        self.save_config(config)
        print("SMTP configuration saved successfully")
    
    def test_connection(self):
        """Test SMTP connection"""
        try:
            if self.config.get('use_tls'):
                server = smtplib.SMTP(self.config['smtp_host'], self.config['smtp_port'])
                server.starttls()
            else:
                server = smtplib.SMTP_SSL(self.config['smtp_host'], self.config['smtp_port'])
            
            server.login(self.config['smtp_username'], self.config['smtp_password'])
            server.quit()
            print("SMTP connection successful!")
            return True
        except Exception as e:
            print(f"SMTP connection failed: {e}")
            return False
    
    def send_email(self, to_email, subject, html_content, text_content=None):
        """Send email"""
        msg = MIMEMultipart('alternative')
        msg['Subject'] = subject
        msg['From'] = self.config['smtp_username']
        msg['To'] = to_email
        
        # Add text version if provided
        if text_content:
            msg.attach(MIMEText(text_content, 'plain'))
        
        # Add HTML version
        msg.attach(MIMEText(html_content, 'html'))
        
        try:
            if self.config.get('use_tls'):
                server = smtplib.SMTP(self.config['smtp_host'], self.config['smtp_port'])
                server.starttls()
            else:
                server = smtplib.SMTP_SSL(self.config['smtp_host'], self.config['smtp_port'])
            
            server.login(self.config['smtp_username'], self.config['smtp_password'])
            server.send_message(msg)
            server.quit()
            print(f"Email sent successfully to {to_email}")
            return True
        except Exception as e:
            print(f"Failed to send email: {e}")
            return False
    
    def generate_wordpress_smtp_plugin_config(self):
        """Generate WordPress SMTP plugin configuration"""
        wp_config = f"""
/* WP Mail SMTP Configuration */
define('WPMS_ON', true);
define('WPMS_SMTP_HOST', '{self.config.get('smtp_host', '')}');
define('WPMS_SMTP_PORT', {self.config.get('smtp_port', 587)});
define('WPMS_SSL', '{'tls' if self.config.get('use_tls') else 'ssl'}');
define('WPMS_SMTP_AUTH', true);
define('WPMS_SMTP_USER', '{self.config.get('smtp_username', '')}');
define('WPMS_SMTP_PASS', '{self.config.get('smtp_password', '')}');
"""
        
        output_file = Path('wp-mail-smtp-config.php')
        with open(output_file, 'w') as f:
            f.write(wp_config)
        
        print(f"WordPress SMTP configuration saved to {output_file}")
        print("Add this to your wp-config.php file")

def main():
    import argparse
    
    parser = argparse.ArgumentParser(description='Email Automation Setup')
    parser.add_argument('--setup', action='store_true', help='Setup SMTP configuration')
    parser.add_argument('--test', action='store_true', help='Test SMTP connection')
    parser.add_argument('--generate-wp-config', action='store_true', help='Generate WordPress SMTP config')
    parser.add_argument('--host', help='SMTP host')
    parser.add_argument('--port', type=int, help='SMTP port')
    parser.add_argument('--username', help='SMTP username')
    parser.add_argument('--password', help='SMTP password')
    parser.add_argument('--no-tls', action='store_true', help='Disable TLS')
    
    args = parser.parse_args()
    
    automation = EmailAutomation()
    
    if args.setup:
        if not all([args.host, args.port, args.username, args.password]):
            print("Error: --host, --port, --username, and --password are required for setup")
            return
        
        automation.setup_smtp(
            args.host,
            args.port,
            args.username,
            args.password,
            not args.no_tls
        )
    
    if args.test:
        automation.test_connection()
    
    if args.generate_wp_config:
        automation.generate_wordpress_smtp_plugin_config()

if __name__ == '__main__':
    main()
