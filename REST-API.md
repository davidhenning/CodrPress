# CodrPress REST interface

## Features ##

- Full JSON support for incoming and outgoing data
- [HTTP digest authentication](http://en.wikipedia.org/wiki/Digest_access_authentication)

## Methods

### Get posts

#### Route

`GET /posts/`

Optional GET parameters:

- `limit`: show only # entries (default: 10)
- `offset`: skip # entries (default: 0)

#### Response

```json
{
    "status": 200,
    "time": "2012-10-28 22:11:37",
    "request": {
        "method": "GET",
        "url": "/posts/"
    },
    "response": {
        "total": 14,
        "found": 10,
        "documents": [
            {
                "_id": "508d90539200ce5810000002"
            }
        ]
    }
}
```

### Get post

#### Route

`GET /post/:id`

`:id` is the 24 characters long hexadecimal MongoId of a post

#### Response

```json
{
    "status": 200,
    "time": "2012-10-28 22:11:37",
    "request": {
        "method": "GET",
        "url": "/post/508d90539200ce5810000002/"
    },
    "response": {
        "total": 1,
        "found": 1,
        "documents": [
            {
                "_id": "508d90539200ce5810000002"
            }
        ]
    }
}
```

### Insert post

#### Route

`PUT /post/`

#### Request

```json
{
	"payload": {
		"title": "rest",
		"body": "rest",
		"body_html": null,
		"slugs": ["rest"],
		"status": "published",
		"disqus": false,
		"tags": ["REST"]
	}
}
```

#### Response

```json
{
    "status": 201,
    "time": "2012-10-28 22:11:37",
    "request": {
        "method": "PUT",
        "url": "/post/"
    },
    "response": {
        "action": "insert",
        "documentId": "508d90539200ce5810000002",
        "documentUri": "/post/508d90539200ce5810000002/"
    }
}
```

### Update post

#### Route

`POST /post/:id/`

#### Request

```json
{
	"payload": {
		"title": "rest",
		"body": "rest",
		"body_html": null,
		"slugs": ["rest"],
		"status": "published",
		"disqus": false,
		"tags": ["REST"]
	}
}
```

#### Response

```json
{
    "status": 202,
    "time": "2012-10-28 22:11:37",
    "request": {
        "method": "POST",
        "url": "/post/508d90539200ce5810000002/"
    },
    "response": {
        "action": "update",
        "documentId": "508d90539200ce5810000002",
        "documentUri": "/post/508d90539200ce5810000002/"
    }
}
```

### Delete post

#### Route

`DELETE /post/:id/`

#### Response

```json
{
    "status": 202,
    "time": "2012-10-28 22:11:37",
    "request": {
        "method": "DELETE",
        "url": "/post/508d90539200ce5810000002/"
    },
    "response": {
        "action": "delete",
        "documentId": "508d90539200ce5810000002"
    }
}
```