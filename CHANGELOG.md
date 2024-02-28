# Changelog

## [Unreleased]

## [0.2.4] - 2024-02-28
### Fixed
- Fixed efabrica/graphql version from 0.2.2 to ^0.2.2

## [0.2.3] - 2024-02-06
### Added
- Support for morph relations

## [0.2.2] - 2024-01-26
### Added
- Support for having conditions
- Literal condition values for first party mode

### Changed
- Order name can be unescaped in first party mode

## [0.2.1] - 2023-07-03
### Added
- Null and NotNull where comparators

## [0.2.0] - 2023-04-06
### Added
- Nette DI extension
- AdditionalResponseData to NetteDatabaseResolverFactory and DatabaseResolver [BC]
- Debug exceptions to resolvers 
- Logging of SQL queries to addition debug data in DatabaseResolver

### Changed
- NetteDatabaseSchemaLoader accepts NetteDatabaseResolverFactoryInterface instead of NetteDatabaseResolverFactory [BC]

### Removed
- Example config.neon [BC]

## [0.1.0] - 2022-10-24
### Added
- Nette database schema loader
- Nette database resolvers

[Unreleased]: https://github.com/efabrica-team/nette-graphql/compare/0.2.4...main
[0.2.4]: https://github.com/efabrica-team/nette-graphql/compare/0.2.3...0.2.4
[0.2.3]: https://github.com/efabrica-team/nette-graphql/compare/0.2.2...0.2.3
[0.2.2]: https://github.com/efabrica-team/nette-graphql/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/efabrica-team/nette-graphql/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/efabrica-team/nette-graphql/compare/0.1.0...0.2.0
[0.1.0]: https://github.com/efabrica-team/nette-graphql/compare/0.0.0...0.1.0
