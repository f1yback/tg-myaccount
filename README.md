# Telegram VPN Personal Account Bot

A Telegram bot service for managing VPN configurations with user personal accounts.

## Features

- **Free trial**: New users can get a 24-hour VPN configuration
- **Balance management**: Top up balance and view active configurations
- **Protocol support**: WireGuard and VLESS + Reality
- **Webhook integration**: Message processing via Telegram webhooks

## Requirements

- Docker and Docker Compose
- PHP 8.2+
- PostgreSQL 15+

## Installation and Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd tg-myaccount
   ```

2. **Configure environment variables**
   ```bash
   cp .env.example .env
   # Edit .env file with your settings
   ```

3. **Run with Docker**
   ```bash
   make up
   ```

4. **Install dependencies**
   ```bash
   make shell
   composer install
   ```

5. **Create database and run migrations**
   ```bash
   make migrate
   ```

6. **Configure Telegram webhook**
   ```bash
   make shell
   bin/console telegram:set-webhook https://your-domain.com/webhook
   ```

## Makefile Commands

- `make up` - Start all services
- `make down` - Stop all services
- `make build` - Build services
- `make shell` - Access PHP container
- `make test` - Run tests
- `make lint` - Code analysis
- `make migrate` - Run migrations
- `make fixtures` - Load test data

## Project Structure

```
src/
├── Command/                 # Console commands
├── Controller/             # HTTP controllers
├── DTO/                    # Data Transfer Objects
│   └── Telegram/           # Telegram API DTOs
├── Entity/                 # Doctrine entities
├── Repository/             # Data repositories
│   ├── User/               # User repositories
│   └── Vpn/                # VPN repositories
├── Service/                # Business logic
│   ├── Telegram/           # Telegram bot services
│   │   ├── Command/        # Command Pattern for bot commands
│   │   │   ├── AbstractBotCommand.php       # Abstract bot command class
│   │   │   ├── AbstractCallbackCommand.php  # Abstract callback command class
│   │   │   ├── BotCommandFactory.php        # Main command factory
│   │   │   ├── CallbackCommandFactory.php   # Callback command factory
│   │   │   ├── CallbackQueryCommand.php     # All callback queries handler
│   │   │   ├── StartCommand.php             # /start command
│   │   │   ├── FreeTrialCallbackCommand.php # Free trial callback
│   │   │   ├── MyConfigsCallbackCommand.php # My configs callback
│   │   │   ├── AddBalanceCallbackCommand.php # Add balance callback
│   │   │   ├── WireGuardProtocolCallbackCommand.php # WireGuard protocol callback
│   │   │   └── VlessRealityProtocolCallbackCommand.php # VLESS Reality protocol callback
│   │   ├── TelegramBotApiAdapter.php    # Telegram API adapter
│   │   ├── TelegramBotHandler.php       # Main handler
│   │   └── TranslationService.php       # Translation service
│   ├── User/               # User management
│   └── Vpn/                # VPN services
├── Migrations/             # Database migrations
└── Tests/                  # Unit and feature tests

translations/               # Translation files
├── messages.en.yaml        # English translations
└── messages.ru.yaml        # Russian translations
```

## API Integration

### VPN Provider API

The project integrates with an external VPN API for configuration generation:

- `POST /configs` - Create new configuration
- `DELETE /configs/{id}` - Delete configuration
- `GET /configs/{id}` - Get configuration status

### Telegram Bot API

- **Fully closed behind interface** (`TelegramBotApiInterface`)
- `/start` - Start working with the bot
- Inline keyboard for protocol selection
- Balance and configuration management
- **Multilingual support** (RU/EN) with automatic user language detection

## Development

### SOLID Principles and Design Patterns

The project follows SOLID principles and uses proven patterns:

#### SOLID Principles:
- **S** (Single Responsibility): Each class has one responsibility
- **O** (Open/Closed): Classes are open for extension, closed for modification
- **L** (Liskov Substitution): All interface implementations are interchangeable
- **I** (Interface Segregation): Separate interfaces for different responsibility zones
- **D** (Dependency Inversion): All dependencies are inverted through interfaces

#### Architectural Patterns:
- **Command Pattern**: Bot command processing through separate command classes
  - Main commands (StartCommand)
  - Callback commands (FreeTrialCallbackCommand, MyConfigsCallbackCommand, etc.)
- **Factory Pattern**:
  - `BotCommandFactory` for creating main bot commands
  - `CallbackCommandFactory` for creating callback commands
- **Adapter Pattern**: `TelegramBotApiAdapter` for external Telegram API abstraction
- **Repository Pattern**: Data access through repository interfaces
- **DTO Pattern**: Data transfer between layers via Data Transfer Objects

### Testing

```bash
# Run all tests
make test

# Run specific test
make shell
./vendor/bin/phpunit tests/Unit/Entity/UserTest.php
```

## Security

- Strict typing everywhere
- Input data validation
- SQL injection protection via Doctrine
- Secure webhook request handling

## Deployment

1. Configure domain and SSL certificate
2. Update environment variables for production
3. Run migrations
4. Configure Telegram webhook
5. Set up monitoring and logging
