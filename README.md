NgramSearch
===========

NgramSearch is a key-value store with fuzzy lookup capabilities based on [ngrams]. It can be used as a foundation to build reasonably fast fuzzy search applications for product names, book titles or similar things. NgramSearch built with pure PHP and it's REST Api is inspired by Elasticsearch. There is also a [demo frontend] with 15.000 indexed products (currently in german).

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
### Create an index
To create a new index, send a `POST` request with the new indexe's name to the API endpoint `create_index`:
```
POST https://foo.example/create_index HTTP/1.1
Host: foo.example
Content-Type: application/x-www-form-urlencoded
Content-Length: 18

index_name=MyIndex
```
This will create a new resource URI `/MyIndex`.

### Store your first key-value-pair
To add a new key-value-pair, send a `POST` request to the newly created resource URI followed by `/add`.
```
POST https://foo.example/MyIndex/add HTTP/1.1
Host: foo.example
Content-Type: application/x-www-form-urlencoded
Content-Length: 56

key=Acme%20Jet%20Propelled%20Pogo%20Stick&value=12345678
```
In most cases, you will use NgramSearch like here. The key would be a product name, a book title, a street address. As value, you would store the item's id from your main database.

### Query the index
In order to make this test sense, you should add some items to your index before you start a query, or you import the provided sample file.

To query an index, send a `GET` request to the index's URI followed by `/query/` and your search string:
```
GET https://foo.example/MyIndex/query/{search_string} HTTP/1.1
Host: foo.example
```
(Replace {search_string} with any urlencoded test string)

In case you have some indexed items, and their keys share any common ngrams with your query string, the API will respond with a json encoded item list, descending ordered by the count of common ngrams:
```javascript
{
    "data": [
        {
            "id": 123,
            "key": "Acme Jet Propelled Pogo Stick",
            "value": "12345678",
            "ngrams_hit": 5,
            "ngram_details": [/*...*/]
        },
        {
            "id": 345,
            "key": "Acme Jet Propelled Unicycle",
            "value": "12345679",
            "ngrams_hit": 4,
            "ngram_details": [/*...*/]
        },
        /*...*/
    ],
    "meta": {/*...*/},
    "links": {/*...*/}
}
```







Basically, NgramIndex is a key-value-store. You can 

### Test



[demo frontend]: http://ngram-search-demo.benjamin-hosseinian.de 
[demo frontend repo]: https://github.com/bnjmnhssnn/NgramSearchDemo
[ngrams]: https://en.wikipedia.org/wiki/N-gram "n-grams"
