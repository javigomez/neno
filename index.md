---
layout: index
---
### Welcome to the Neno Documentation

```
The documentation is not ready yet. Please be patient!
```


## Contents
* [Components not fully compatible with Neno](#components-not-fully-compatible-with-neno)

## Getting Started With Neno

### Installing & setting up Neno

Neno is very easy to setup and only takes a few minutes to install. We have created a walkthrough guide below to to guide you through the process. 
##### Step 1 Select Source language.
The first thing you will be asked to do is to Select your source Language. This is the language you will use to translate from. If your website is already multilingual then choose the main language you create content in. We recommend that you select English as your source language whenever possible, as the quality of external machine translation is often better.


## Components not fully compatible with Neno

### JReviews
This component is not fully compatible with Neno because it has a table called \#__jreviews_content which mixes content in several languages. That table contains fields to store tags in different languages such as tagesX (Spanish tags), tagenX (English tags), tagroX ( Romanian tags), tagdeX (German tags), tagitX (Italian tags). 

The correct method for implementing tags in Joomla is to use com_tags or create a separate table for tags and use the “language” field. 
