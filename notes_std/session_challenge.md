# Session Challenge

A session challenge is used to verify the integrity of the client that originally established
the session. This processes uses TOTP (Time-based One-Time Password) and SHA1 hashing.

## How it works

When you establish a session, for every method that requires a session parameter you must
pass on the session object with the hash challenge being completed.


### Establishing a session

For establishing a session, you must send the server the following parameters

| Parameter           | Description                                                                                                                                                                                |
|---------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Client Public Hash  | A random but *permanent* hash of your client, it must be the  same whenever you use this session and cannot change.                                                                        |
| Client Private Hash | A random but *permanent* private hash of your client, it must be the same whenever you use this session and cannot change,  you only send this value once during the creation of a session |
| Platform            | The platform your client is running on, eg; Linux, Windows, etc. This is used for security reasons for the user to identify what clients has had access to their account.                  |
| Client Name         | The name of the client, eg; SuperClient. This is used for security reasons for the user to identify what clients has had access to their account                                           |
| Client Version      | The version of the client, this is used for security reasons for the user to identify what clients has had access to their account, this parameter can be changed later down the road.     |

Upon this, you will receive a session established object

| Parameter      | Description                                                                                                  |
|----------------|--------------------------------------------------------------------------------------------------------------|
| Session ID     | A unique ID for the session                                                                                  |
| Challenge Hash | A string of random data but unique to this session, it does not change. Your client must keep this a secret. |


### Session Identification

Whenever you invoke a request that requires a Session identification object, you would need
to send this object with the challenge answer completed.

| Parameter        | Description                                                        |
|------------------|--------------------------------------------------------------------|
| Session ID       | The unique ID for the session you are using                        |
| Client Hash      | The hash of your client (public)                                   |
| Challenge Answer | The answer to the Challenge Hash, this changes every minute or so. |


## Challenge Algorithm

The challenge algorithm is a simple way to validate the integrity of the client and 
must always be up-to-date, the server will take in consideration that time can be out of sync
for a minute in past or future due to network latency or processing time. but the 
general gist is that the Challenge Algorithm is based off TOTP (Time-based One-Time Password),
the same algorithm used for Two-Factor authentication but instead of sending a 6 digit
code, you are to send a hash based around the results of the algorithm.

The challenge hash returned when you establish a session is used as the secret for
generating TOTP codes, your client private hash is used in combination using SHA1 to
create a challenge answer. `sha1( totp(challenge_hash) + client_private_hash )`

### Time-based One-Time Password Resources

 - https://en.wikipedia.org/wiki/Time-based_One-Time_Password
 - https://datatracker.ietf.org/doc/html/rfc6238

### Time-based One-Time Password libraries

 - https://github.com/pyauth/pyotp (Python)
 - https://github.com/soveran/totp (Ruby)
 - https://github.com/suvash/one-time (Clojure)
 - https://github.com/LanceGin/jsotp (Javascript)
 - https://github.com/tilkinsc/COTP (C/C++)
 - https://github.com/pedrosancao/php-otp (PHP)
 - https://github.com/samdjstevens/java-totp (Java)
 - https://github.com/kspearrin/Otp.NET (C#)