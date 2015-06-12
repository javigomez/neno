---
layout: index
---
### Welcome to the Neno Documentation

```
The documentation is not ready yet. Please be patient!
```


## Contents
* [Components not fully compatible with Neno](#components-not-fully-compatible-with-neno)


## Components not fully compatible with Neno

### JReviews
This component is not fully compatible with Neno because it has a table called \#__jreviews_content which mixes content in several languages. That table contains fields to store tags in different languages such as tagesX (Spanish tags), tagenX (English tags), tagroX ( Romanian tags), tagdeX (German tags), tagitX (Italian tags). 

The correct method for implementing tags in Joomla is to use com_tags or create a separate table for tags and use the “language” field. 
