# Changelog

## 2.0.0 (2026-11-27)

The Poggit plugin version for the original [pmmp/PocketMine-MP](https://github.com/pmmp/PocketMine-MP) 5.x (1.0.1) is kept
on the [`legacy/pmmp-5.44`](https://github.com/presentkim-pm/SilkPickBlock/tree/legacy/pmmp-5.44) branch.

### ⚠️ Breaking changes
- Targets [axolotl-pm/PocketMine-MP](https://github.com/axolotl-pm/PocketMine-MP) `5.51` (BedrockProtocol 64,
  Bedrock `1.26.60`) and **PHP 8.4+**.
- Distributed as a phar from GitHub Releases instead of Poggit.

### 🔄 Changes
- `composer.json` added (Composer is only used for development and testing); `plugin.yml` has
  `src-namespace-prefix` and the source files are no longer nested in `src/kim/present/silkpickblock/`.
- `Main` is now `final` and `onEnable()` is `protected`.
- PHPUnit tests (class loading and `plugin.yml` consistency).

### 🐛 Fixes
- Enabling the plugin no longer fails with a `TypeError` when the data folder cannot be scanned.
