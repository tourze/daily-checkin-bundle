# 每日签到模块

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://php.net)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E6.4-blue)](https://symfony.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](LICENSE)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](LICENSE)

[English](README.md) | [中文](README.zh-CN.md)

## 目录

- [功能特性](#功能特性)
- [依赖要求](#依赖要求)
- [安装](#安装)
- [配置](#配置)
- [使用方法](#使用方法)
- [API 文档](#api-文档)
- [架构设计](#架构设计)
- [开发指南](#开发指南)
- [测试](#测试)
- [贡献指南](#贡献指南)
- [许可证](#许可证)

一个功能完善的 Symfony 每日签到系统，支持活动管理、奖励系统和奖品分发。

## 功能特性

- **活动管理**：创建和管理每日签到活动
- **灵活签到模式**：支持普通签到和连续签到模式
- **奖励系统**：可配置的奖励系统（金币、优惠券等）
- **奖品分发**：自动奖品计算和分发
- **管理界面**：完整的 EasyAdmin CRUD 控制器
- **JSON-RPC API**：开箱即用的 API 接口
- **事件系统**：可扩展的事件驱动架构
- **数据追踪**：完整的审计跟踪和用户记录

## 依赖要求

此模块需要以下 Symfony 组件和 PHP 扩展：

### 核心依赖
- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本
- EasyAdmin Bundle 4.x

### Tourze 依赖
- `tourze/doctrine-snowflake-bundle` - 雪花ID生成
- `tourze/doctrine-timestamp-bundle` - 时间戳管理
- `tourze/doctrine-user-bundle` - 用户追踪
- `tourze/json-rpc-core` - JSON-RPC API 基础
- `tourze/coupon-core-bundle` - 优惠券集成（可选）

## 安装

```bash
composer require tourze/daily-checkin-bundle
```

## 配置

模块使用 Symfony 的服务容器进行配置。所有服务都是自动配置的。

### 实体关系

- `Activity`：主要的签到活动
- `Award`：每日奖励配置
- `Record`：用户签到记录
- `Reward`：分发给用户的奖励

### 事件系统

模块分发以下事件：

- `AfterCheckinEvent`：成功签到后
- `BeforeReturnCheckinActivityEvent`：返回活动数据前
- `BeforeReturnCheckinAwardsEvent`：返回奖励数据前
- `BeforeOrPrizeReturnEvent`：奖品分发前

## 快速开始

1. **启用模块**，在 `config/bundles.php` 中添加：

```php
return [
    // ...
    DailyCheckinBundle\DailyCheckinBundle::class => ['all' => true],
];
```

2. **更新数据库结构**：

```bash
php bin/console doctrine:schema:update --force
```

3. **加载示例数据**（可选）：

```bash
php bin/console doctrine:fixtures:load --group=daily-checkin
```

## 使用方法

### 创建签到活动

```php
use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Enum\CheckinType;

$activity = new Activity();
$activity->setTitle('月度签到挑战');
$activity->setDescription('每日签到赢取奖励');
$activity->setType(CheckinType::Continuous);
$activity->setStartDate(new \DateTime('2024-01-01'));
$activity->setEndDate(new \DateTime('2024-01-31'));

$entityManager->persist($activity);
$entityManager->flush();
```

### 为活动添加奖励

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

### JSON-RPC API 接口

- `DoCheckin` - 执行每日签到
- `GetDailyCheckinActivityInfo` - 获取活动详情
- `GetCheckinAwards` - 获取可用奖励
- `GetUserCheckinRecords` - 获取用户签到历史
- `SubmitCheckinAward` - 领取奖励

### API 调用示例

```javascript
// 签到 API 调用
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

## 高级用法

### 自定义事件监听器

监听签到事件以扩展功能：

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
        // 签到后的自定义逻辑
    }
}
```

### 自定义奖励类型

使用自定义类型扩展奖励系统：

```php
use DailyCheckinBundle\Enum\RewardType;

enum CustomRewardType: string
{
    case EXPERIENCE = 'experience';
    case BADGE = 'badge';
}
```

### 与其他模块集成

该模块可以无缝集成：

- **Credit Bundle**：用于积分/点数奖励
- **Coupon Bundle**：用于优惠券分发
- **Lock Service Bundle**：用于并发访问控制

## API 文档

### JSON-RPC 方法

模块提供以下 JSON-RPC 方法：

#### DoCheckin
执行当前用户的每日签到。

**参数：**
- `activityId` (string)：签到活动的ID
- `checkinDate` (string, 可选)：签到日期（默认为当前日期）

**返回：**签到记录信息

#### GetDailyCheckinActivityInfo
获取签到活动的详细信息。

**参数：**
- `activityId` (string)：活动ID

**返回：**活动详情包括配置和状态

#### GetCheckinAwards
获取特定活动的可用奖励。

**参数：**
- `activityId` (string)：活动ID

**返回：**可用奖励列表

#### GetUserCheckinRecords
获取用户的签到历史记录。

**参数：**
- `activityId` (string)：活动ID
- `limit` (int, 可选)：返回记录数量

**返回：**用户签到记录列表

#### SubmitCheckinAward
从签到系统领取奖励。

**参数：**
- `activityId` (string)：活动ID
- `awardId` (string)：要领取的奖励ID

**返回：**奖励分发结果

## 架构设计

### 核心组件

#### 实体类
- **Activity**：表示签到活动，包含开始/结束日期和配置
- **Award**：定义每个签到日可用的奖励
- **Record**：跟踪个人用户签到事件
- **Reward**：记录分发给用户的奖励

#### 服务类
- **CheckinPrizeService**：处理奖励计算和分发逻辑
- **ActivityRepository**：活动的数据库操作
- **RecordRepository**：管理签到记录
- **AwardRepository**：处理奖励配置
- **RewardRepository**：管理奖励分发

#### 事件系统
模块使用 Symfony 的事件分发器来实现可扩展性：

- **AfterCheckinEvent**：成功签到后触发
- **BeforeReturnCheckinActivityEvent**：返回活动数据前
- **BeforeReturnCheckinAwardsEvent**：返回奖励数据前
- **BeforeOrPrizeReturnEvent**：奖品分发前

#### 枚举类
- **CheckinType**：普通或连续签到模式
- **RewardType**：奖励类型（金币、优惠券等）
- **RewardGetType**：奖励分发方式

### 数据库架构

模块创建以下数据表：
- `daily_checkin_activity`：签到活动
- `daily_checkin_award`：奖励配置
- `daily_checkin_record`：用户签到记录
- `daily_checkin_reward`：分发的奖励

## 开发指南

### 前置要求
- PHP 8.1+ 及所需扩展
- Composer 依赖管理工具
- Symfony 6.4+ 应用程序

### 搭建开发环境

1. 克隆仓库：
```bash
git clone <repository-url>
cd daily-checkin-bundle
```

2. 安装依赖：
```bash
composer install
```

3. 设置数据库：
```bash
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load --group=daily-checkin
```

### 代码风格
遵循 PSR-12 编码标准和 Symfony 最佳实践。

### 运行静态分析
```bash
composer run phpstan
```

## 测试

### 运行测试
执行完整测试套件：
```bash
composer test
```

### 测试覆盖率
模块保持 100% 测试覆盖率，包括：
- 所有实体和服务的单元测试
- JSON-RPC 过程的集成测试
- 管理界面的控制器测试

### 编写测试
- 集成测试使用 `AbstractIntegrationTestCase`
- 遵循 `/tests` 目录中的现有测试模式
- 确保所有新代码都包含适当的测试

## 贡献指南

1. Fork 仓库
2. 创建功能分支：`git checkout -b feature/amazing-feature`
3. 进行更改并添加测试
4. 确保所有测试通过：`composer test`
5. 提交 Pull Request

### 开发指导原则
- 遵循 Symfony 和 PHP 最佳实践
- 为新功能编写全面的测试
- 更新 API 变更的文档
- 使用语义化版本控制进行发布

## 管理界面

模块提供 EasyAdmin 控制器用于：

- 活动管理 (`/admin/daily-checkin-activity`)
- 奖励配置 (`/admin/daily-checkin-award`)
- 签到记录 (`/admin/daily-checkin-record`)
- 奖励分发 (`/admin/daily-checkin-reward`)

## 许可证

此模块在 MIT 许可证下发布。详情请查看 [LICENSE](LICENSE) 文件。

## 参考文档

- [产品设计：签到功能如何设计？](http://www.woshipm.com/pd/1082405.html)
- [签到系统设计思路](https://www.jianshu.com/p/9b15151af2d2)
- [如何设计一个签到系统](http://www.woshipm.com/pd/4298167.html)
- [签到系统的产品设计](https://blog.csdn.net/zoezhi/article/details/115451750)
