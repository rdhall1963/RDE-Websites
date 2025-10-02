# Contributing to RDE-Websites

Thank you for your interest in contributing to RDE-Websites! We welcome contributions from the community.

## How to Contribute

### Reporting Issues

If you find a bug or have a feature request:

1. Check if the issue already exists in the [Issues](https://github.com/rdhall1963/RDE-Websites/issues) section
2. If not, create a new issue with a clear title and description
3. Include steps to reproduce (for bugs) or use cases (for features)

### Submitting Changes

1. **Fork the repository**
   ```bash
   git clone https://github.com/rdhall1963/RDE-Websites.git
   cd RDE-Websites
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**
   - Follow the existing code style
   - Add comments where necessary
   - Update documentation if needed

4. **Test your changes**
   ```bash
   npm run test
   composer test
   ```

5. **Commit your changes**
   ```bash
   git add .
   git commit -m "Add: your feature description"
   ```

6. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Create a Pull Request**
   - Go to the original repository
   - Click "New Pull Request"
   - Select your branch
   - Provide a clear description of your changes

## Code Style Guidelines

### PHP
- Follow WordPress Coding Standards
- Use meaningful variable and function names
- Add PHPDoc comments for functions

### JavaScript
- Use ES6+ syntax
- Follow Airbnb JavaScript Style Guide
- Use meaningful variable names

### Python
- Follow PEP 8 style guide
- Use type hints where appropriate
- Add docstrings to functions

## Testing

Before submitting a pull request:

1. Run all tests: `npm test`
2. Check code style: `npm run lint`
3. Test manually in a development environment

## Documentation

- Update README.md if adding new features
- Update docs/DOCUMENTATION.md for detailed changes
- Add inline comments for complex logic

## Questions?

If you have questions, feel free to:
- Open an issue
- Contact the maintainers

Thank you for contributing!
