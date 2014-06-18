# Introduction
This application lets you view your repositories at GitHub and whether or not
you watch them yourself. It seems this is impossible to do in the GitHub 
interface itself in one convenient location.

# Installation
You need both Bower and Composer to install the dependencies.

First Composer:

    $ composer.phar install

And then Bower:

    $ bower install

# Configuration
Copy `config.php.default` to `config.php` and modify the configuration to
match the client configuration obtained from the GitHub website after you 
[register](https://github.com/settings/application) your client. Also update
the URL there to point to your `index.php`. The callback URL you register at
GitHub should look like this: 
`https://fkooman.pagekite.me/fkooman/github-watching/callback.php`. Make sure 
you update it to your location.
