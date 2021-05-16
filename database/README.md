# Database Tables

These are generated SQL files used to building the MySQL tables
that this whole library will use.


# Priorities

Set up the tables in the recommended order to avoid missing
foreign key checks.

| Priority | Table              |
|----------|--------------------|
| 1        | users              |
| 2        | sessions           |
| 3        | follower_data      |
| 4        | follower_states    |
| 5        | coa_authentication |
| 6        | cookies            |
| 7        | posts              |
| 8        | likes              |
| 9        | quotes             |
| 10       | replies            |
| 11       | reposts            |
| 12       | user_timelines     |