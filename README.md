# Daily Checkin Bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E6.4-blue)](https://symfony.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](LICENSE)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](LICENSE)

[English](README.md) | [中文](README.zh-CN.md)

## Table of Contents

- [Features](#features)
- [Dependencies](#dependencies)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Architecture](#architecture)
- [Development](#development)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

A comprehensive daily check-in system for Symfony applications with activity 
management, rewards, and prize distribution.

## Features

- **Activity Management**: Create and manage daily check-in activities
- **Flexible Check-in Types**: Support for normal and continuous check-in modes
- **Reward System**: Configurable rewards with different types (coins, coupons, etc.)
- **Prize Distribution**: Automatic prize calculation and distribution
- **Admin Interface**: Complete EasyAdmin CRUD controllers
- **JSON-RPC API**: Ready-to-use API endpoints for mobile/web integration
- **Event System**: Extensible event-driven architecture
- **Data Tracking**: Full audit trail with user tracking and timestamps

## Dependencies

This bundle requires the following Symfony bundles and PHP extensions:

### Core Dependencies
- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher
- EasyAdmin Bundle 4.x

### Tourze Dependencies
- `tourze/doctrine-snowflake-bundle` - Snowflake ID generation
- `tourze/doctrine-timestamp-bundle` - Timestamp management
- `tourze/doctrine-user-bundle` - User tracking
- `tourze/json-rpc-core` - JSON-RPC API foundation
- `tourze/coupon-core-bundle` - Coupon integration (optional)

## Installation

```bash
composer require tourze/daily-checkin-bundle
```

## Configuration

The bundle uses Symfony's service container for configuration. All services are auto-configured.

### Entity Relationships

- `Activity`: Main check-in activity
- `Award`: Rewards configuration for each day
- `Record`: User check-in records
- `Reward`: Distributed rewards to users

### Event System

The bundle dispatches several events:

- `AfterCheckinEvent`: After successful check-in
- `BeforeReturnCheckinActivityEvent`: Before returning activity data
- `BeforeReturnCheckinAwardsEvent`: Before returning awards data
- `BeforeOrPrizeReturnEvent`: Before prize distribution

## Quick Start

1. **Enable the bundle** in your `config/bundles.php`:

```php
return [
    // ...
    DailyCheckinBundle\DailyCheckinBundle::class => ['all' => true],
];
```

2. **Update your database schema**:

```bash
php bin/console doctrine:schema:update --force
```

3. **Load sample data** (optional):

```bash
php bin/console doctrine:fixtures:load --group=daily-checkin
```

## Usage

### Create a Check-in Activity

```php
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Enum\CheckinType;

$activity = new Activity();
$activity->setTitle('Monthly Check-in Challenge');
$activity->setDescription('Check in daily to earn rewards');
$activity->setType(CheckinType::Continuous);
$activity->setStartDate(new \DateTime('2024-01-01'));
$activity->setEndDate(new \DateTime('2024-01-31'));

$entityManager->persist($activity);
$entityManager->flush();
```

### Add Rewards to Activity

```php
use DailyCheckinBundle\Entity\Award;
use DailyCheckinBundle\Enum\RewardType;

$award = new Award();
$award->setActivity($activity);
$award->setDay(1);
$award->setType(RewardType::Coin);
$award->setValue(100);
$award->setQuantity(1);

$entityManager->persist($award);
$entityManager->flush();
```

### JSON-RPC API Endpoints

- `DoCheckin` - Perform daily check-in
- `GetDailyCheckinActivityInfo` - Get activity details
- `GetCheckinAwards` - Get available rewards
- `GetUserCheckinRecords` - Get user's check-in history
- `SubmitCheckinAward` - Claim rewards

### Example API Call

```javascript
// Check-in API call
fetch('/api/jsonrpc', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    jsonrpc: '2.0',
    method: 'DoCheckin',
    params: {
      activityId: '1234567890',
      checkinDate: '2024-01-15'
    },
    id: 1
  })
});
```

## Advanced Usage

### Custom Event Listeners

Listen to check-in events to extend functionality:

```php
use DailyCheckinBundle\Event\AfterCheckinEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckinSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AfterCheckinEvent::class => 'onAfterCheckin',
        ];
    }

    public function onAfterCheckin(AfterCheckinEvent $event): void
    {
        $record = $event->getRecord();
        // Custom logic after check-in
    }
}
```

### Custom Reward Types

Extend the reward system with custom types:

```php
use DailyCheckinBundle\Enum\RewardType;

enum CustomRewardType: string
{
    case EXPERIENCE = 'experience';
    case BADGE = 'badge';
}
```

### Integration with Other Bundles

The bundle integrates seamlessly with:

- **Credit Bundle**: For credit/point rewards
- **Coupon Bundle**: For coupon distribution
- **Lock Service Bundle**: For concurrent access control

## API Documentation

### JSON-RPC Methods

The bundle provides the following JSON-RPC methods:

#### DoCheckin
Perform a daily check-in for the current user.

**Parameters:**
- `activityId` (string): The ID of the check-in activity
- `checkinDate` (string, optional): The date to check in (defaults to current date)

**Returns:** Check-in record information

#### GetDailyCheckinActivityInfo
Get detailed information about a check-in activity.

**Parameters:**
- `activityId` (string): The ID of the activity

**Returns:** Activity details including configuration and status

#### GetCheckinAwards
Get available awards for a specific activity.

**Parameters:**
- `activityId` (string): The ID of the activity

**Returns:** List of available awards

#### GetUserCheckinRecords
Get the user's check-in history.

**Parameters:**
- `activityId` (string): The ID of the activity
- `limit` (int, optional): Number of records to return

**Returns:** List of user's check-in records

#### SubmitCheckinAward
Claim a reward from the check-in system.

**Parameters:**
- `activityId` (string): The ID of the activity
- `awardId` (string): The ID of the award to claim

**Returns:** Reward distribution result

## Architecture

### Core Components

#### Entities
- **Activity**: Represents a check-in campaign with start/end dates and configuration
- **Award**: Defines rewards available for each check-in day
- **Record**: Tracks individual user check-in events
- **Reward**: Records distributed rewards to users

#### Services
- **CheckinPrizeService**: Handles reward calculation and distribution logic
- **ActivityRepository**: Database operations for activities
- **RecordRepository**: Manages check-in records
- **AwardRepository**: Handles award configurations
- **RewardRepository**: Manages reward distribution

#### Event System
The bundle uses Symfony's event dispatcher for extensibility:

- **AfterCheckinEvent**: Triggered after successful check-in
- **BeforeReturnCheckinActivityEvent**: Before returning activity data
- **BeforeReturnCheckinAwardsEvent**: Before returning awards data
- **BeforeOrPrizeReturnEvent**: Before prize distribution

#### Enums
- **CheckinType**: Normal or Continuous check-in modes
- **RewardType**: Types of rewards (coins, coupons, etc.)
- **RewardGetType**: Distribution methods for rewards

### Database Schema

The bundle creates the following tables:
- `daily_checkin_activity`: Check-in activities
- `daily_checkin_award`: Award configurations
- `daily_checkin_record`: User check-in records
- `daily_checkin_reward`: Distributed rewards

## Development

### Prerequisites
- PHP 8.1+ with required extensions
- Composer for dependency management
- Symfony 6.4+ application

### Setup Development Environment

1. Clone the repository:
```bash
git clone <repository-url>
cd daily-checkin-bundle
```

2. Install dependencies:
```bash
composer install
```

3. Set up database:
```bash
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load --group=daily-checkin
```

### Code Style
Follow PSR-12 coding standards and Symfony best practices.

### Running Static Analysis
```bash
composer run phpstan
```

## Testing

### Running Tests
Execute the full test suite:
```bash
composer test
```

### Test Coverage
The bundle maintains 100% test coverage including:
- Unit tests for all entities and services
- Integration tests for JSON-RPC procedures
- Controller tests for admin interfaces

### Writing Tests
- Use `AbstractIntegrationTestCase` for integration tests
- Follow the existing test patterns in `/tests` directory
- Ensure all new code includes appropriate tests

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Ensure all tests pass: `composer test`
5. Submit a pull request

### Development Guidelines
- Follow Symfony and PHP best practices
- Write comprehensive tests for new features
- Update documentation for API changes
- Use semantic versioning for releases

## Admin Interface

The bundle provides EasyAdmin controllers for:

- Activity management (`/admin/daily-checkin-activity`)
- Award configuration (`/admin/daily-checkin-award`)
- Check-in records (`/admin/daily-checkin-record`)
- Reward distribution (`/admin/daily-checkin-reward`)

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## References

- [产品设计：签到功能如何设计？](http://www.woshipm.com/pd/1082405.html)
- [签到系统设计思路](https://www.jianshu.com/p/9b15151af2d2)
- [如何设计一个签到系统](http://www.woshipm.com/pd/4298167.html)
- [签到系统的产品设计](https://blog.csdn.net/zoezhi/article/details/115451750)
