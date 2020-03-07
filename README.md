NgramSearch
===========

NgramSearch is a reasonably fast fuzzy search implementation based on [ngrams]. It is built with pure PHP and it's REST Api is inspired by Elasticsearch. There is also a [demo frontend] with 15.000 indexed products (currently in german).

![PHP Composer](https://github.com/bnjmnhssnn/NgramSearch/workflows/PHP%20Composer/badge.svg)
[![bnjmnhssnn](https://circleci.com/gh/bnjmnhssnn/NgramSearch.svg?style=shield)](https://circleci.com/gh/bnjmnhssnn/NgramSearch)

Install
-------

**1. Clone from Github:**

```sh
git clone https://github.com/bnjmnhssnn/NgramSearch.git
```

**2. Install dependencies:**

```sh
composer install
```

**3. Configuration**

Run the *setup* console command to configure your application. 

```sh
php cli.php setup
```
The configuration will be stored in *src/env.php*.

**4. Create index with sample data (optional)**

Run the *import* console command. 

```sh
php cli.php import
```
You will be promted to select a *.txt* from the *import* directory. Select one of the sample files. Importing large data sets takes it's time, so start with a small one.

NgramSearch requires PHP 7.1 or newer.

Usage
-----

Lorem ipsum dolor sit amet

### Test



[demo frontend]: http://ngram-search-demo.benjamin-hosseinian.de 
[demo frontend repo]: https://github.com/bnjmnhssnn/NgramSearchDemo
[ngrams]: https://en.wikipedia.org/wiki/N-gram "n-grams"
