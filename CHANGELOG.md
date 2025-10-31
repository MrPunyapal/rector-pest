# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release with three core Rector rules for PestPHP
- `ConvertAssertToExpectRule`: Converts PHPUnit assertions to Pest expectations
- `ConvertTestMethodToPestFunctionRule`: Converts PHPUnit test methods to Pest test functions
- `ConvertSetUpToBeforeEachRule`: Converts PHPUnit setUp/tearDown to Pest beforeEach/afterEach
- Comprehensive documentation and examples
- CI/CD pipeline with GitHub Actions
- Code quality tools: PHPStan, ECS, Rector
