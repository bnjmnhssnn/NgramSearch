NgramSearch
===========

NgramSearch is a reasonably fast fuzzy search implementation based on [ngrams]. It is built with pure PHP and it's REST Api is inspired by Elasticsearch. 

Install
-------

1. Clone from Github:

```sh
git clone https://github.com/bnjmnhssnn/NgramSearch.git
```

2. Install dependencies:

```sh
composer install
```

3. Configuration

Run the setup console command to configure your application. 

```sh
php cli.php setup
```
The configuration will be stored in `src/env.php`.

4. Create index with sample data (optional)

Run the import console command. 

```sh
php cli.php import
```
You will be promted to select a `.txt` from the folder `import`. Select one of the sample files. Importing large data sets takes it's time, so start with a small one.

NgramSearch requires PHP 7.1 or newer.

Usage
-----

Lorem ipsum dolor sit amet

### Test




[ngrams]: https://en.wikipedia.org/wiki/N-gram "n-grams"
