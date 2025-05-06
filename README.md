# PHP Article Generator using OpenRouter API

This is a simple PHP application that generates articles using the OpenRouter API, which provides access to various language models.

## Features

- Generate articles of different lengths (short, medium, long)
- Simple and intuitive user interface
- Copy generated articles to clipboard
- Responsive design

## Requirements

- PHP 7.4 or higher
- cURL extension for PHP
- OpenRouter API key

## Installation

1. Clone or download this repository to your web server directory
2. Copy `config-example.php` to `config.php` and add your OpenRouter API key
3. Make sure your web server is configured to run PHP files
4. Access the application through your web browser (e.g., http://localhost/article-generator)

## Getting an OpenRouter API Key

1. Sign up for an account at [OpenRouter](https://openrouter.ai/)
2. Navigate to your account settings to generate an API key
3. Copy the API key and paste it in the `config.php` file

## Usage

1. Enter a topic for your article in the form
2. Select the desired length (short, medium, or long)
3. Click "Generate Article"
4. Once the article is generated, you can copy it to your clipboard using the "Copy to Clipboard" button

## Configuration

You can configure different aspects of the application in the `config.php` file:

- Change the default model (currently set to 'openai/gpt-3.5-turbo')
- Adjust the temperature parameter to control creativity (higher values = more creative)
- Set default article length

You can also modify the system instructions in `article_generator.php` to generate different types of content.

## Security Notes

- Never commit your `config.php` file with your actual API key to a public repository
- The included `.gitignore` file already excludes `config.php` to help prevent accidental exposure
- Always use the provided `config-example.php` as a template

## License

This project is licensed under the MIT License - see the LICENSE file for details.
