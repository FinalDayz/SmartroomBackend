# Database
### Entity
Make a new (or edit existing) entity with:
`php bin/console make:entity`

### Generate migrations
To generate sql scripts with the last edited entity changes:
`php bin/console make:migration`

### Execute migrations
To execute migration scripts that have not yet been ran:
`php bin/console doctrine:migrations:migrate`
#####Migrate down
To migrate down using the latest migration use `pref` behind the command.

## Manual SQL
There are also commands to map database tables to entities:
`php bin/console make:entity --regenerate`

Besides that, the way to execute SQL statements is via the bin/console command:
`php bin/console doctrine:query:sql 'SELECT * FROM Track'`

#symfony commands

### Controller
To create a controller via the command line, 
type ``php bin/console make:controller CreateAccountController``

### Security
A manual way to generate hashed password `php bin/console security:encode-password`