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
    "meta": {
        "status": 200,
        "msg": "OK"
    },
    "response": {
        "posts": [
            {
                "_id": "508d90539200ce5810000002",
                "title": "REST-API"
                ...
            }
        ],
        "total": 14,
        "found": 10
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
    "meta": {
        "status": 200,
        "msg": "OK"
    },
    "response": {
        "posts": [
            {
                "_id": "508d90539200ce5810000002",
                "title": "REST-API"
                ...
            }
        ],
        "total": 1,
        "found": 1
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
    "meta": {
        "status": 201,
        "msg": "Created"
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
    "meta": {
        "status": 202,
        "msg": "Accepted"
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
    "meta": {
        "status": 202,
        "msg": "Accepted"
    },
    "response": {
        "action": "delete",
        "documentId": "508d90539200ce5810000002"
    }
}
```