NgramSearch
===========

NgramSearch is a key-value store with fuzzy lookup capabilities based on [ngrams]. It can be used as a foundation to build reasonably fast fuzzy search applications for product names, book titles or similar things. It's REST Api is inspired by Elasticsearch and follows (mostly) the [json:api] specification. There is also a [demo frontend] with ~50.000 indexed movie titles.

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
You will be promted to select a *.txt* from the *import* directory. Select one of the sample files and start indexing!

NgramSearch requires PHP 7.1 or newer.

How it works
------------
Basically, NgramSearch is a **key-value store**. While there is nothing special about the **values** you can store, **keys** are quite special:

### Keys in NgramSearch
In Redis, for example, you would choose a short descriptive string as key, and maybe extend it with an id, like *user:1234*.
You would then use this exact string to retrieve the stored value.

**NgramSearch is made for fuzzy string matching, and the strings to match are the keys in this key-value store.** 
Good NgramSearch keys are short or medium sized strings, like these 2 examples:

* a product name with brand, e.g. *Acme Jet Propelled Pogo Stick*
* a book title with author, e.g. *Lewis Carroll Alice's Adventures in Wonderland*

Longer keys are also fine, e.g. a short description of the item. The drawback is, long keys tend to lower the search result quality for short search strings. In order to improve the quality, strip filler words from the key, before storing it in NgramSearch.

**Not so good description:**

*Acme Giant Rubber Bands come in all sizes, are fantastically elastic, and are great at tripping road runners (when used properly)*

**Better, with stripped filler words:**

*Acme Giant Rubber Bands all sizes elastic tripping road runners*

All keys will also go through an automatic normalization step before they are stored. At this point, this means replacing any non german accented chars by their non-accented variant, conversion to lowercase and stripping of special chars. It is planned to provide some localized normalization strategies, later.

### Values in NgramSearch
There is not much to say about **values** in NgramSearch: you can store any string as value. A typical example for a value is an item id. You will normally not expose your NgramSearch APIs endpoint directly, so it is sufficient to store a primary key from your main database as value in NgramSearch, and then build the final HTML representation of the result item in your client app.

However, if you protect the critical endpoints, you could expose the API to the public and store complex data structures as values, e.g. your product data as json, or a search result item's HTML representation. You will then gain a performance boost as you save one network request.

NOTE: The provided sample file `/imports/15000_sample_products_german.txt` uses a product name as key **AND** value for demonstration purposes

### Query results
Unlike a usual key-value store, NgramSearch will not return a single, distinct result when you query it. NgramSearch almost always returns a set of possible results. For example, if you ask NgramSearch for the movie *Lost in Translation*, but you spell it utterly wrong, e.g. *Lostin trasnlatin*, it may return a result set containing *Lost in Translation, Lost in Space, Hotel Transsylvania* and *How to be a Latin Lover*. *Lost in Translation* will be on top of the list, because it is most similar to the search string. And that is what qualifies NgramSearch as a foundation for a fuzzy search app.

### Result refinement
NgramSearch follows the [single-responsibility principle] and therefore returns raw, unrefined results, ordered by the count of ngrams that the search string and the result's key have in common. Surprisingly often, this raw output is directly usable and could be presented to the end user. But more often, you want to perform further adjustments on the result set:

* re-order items through advanced similarity algorithms (levenshtein distance etc.)
* remove items below a certain similarity threshold
* re-order or remove items based on your own business rules, e.g. hide currently sold out products
* group items
* optically emphasize parts of the item to indicate the matched string fragment

Such adjustments are out of NgramSearch's scope and must be implemented client-side. NgramSearch provides some statistical data for each result item, that could be used to calculate the individual item relevance.

API walkthrough
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
    data: [
        {
            id: 123,
            key: "Acme Jet Propelled Pogo Stick",
            value: "12345678",
            ngrams_hit: 5,
            ngram_details: [/*...*/]
        },
        {
            id: 345,
            key: "Acme Jet Propelled Unicycle",
            value: "12345679",
            ngrams_hit: 4,
            ngram_details: [/*...*/]
        },
        /*...*/
    ],
    meta: {/*...*/},
    links: {/*...*/}
}
```

[json:api]: https://jsonapi.org
[demo frontend]: http://ngram-search-demo.benjamin-hosseinian.de 
[demo frontend repo]: https://github.com/bnjmnhssnn/NgramSearchDemo
[ngrams]: https://en.wikipedia.org/wiki/N-gram
[single-responsibility principle]: https://en.wikipedia.org/wiki/Single-responsibility_principle
