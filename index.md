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

Once you have selected your source language click next to move to next step.

##### Step 2 Default Settings 
Here you can choose Your default Translation settings. When Neno discovers untranslated content on your site it will assign it to the translation method you select here. You can also select a second translation method; this can be useful if for example you select machine translation as the first method, and want to review the machine translations manually afterwards.These settings can easily be changed later. For now select the translation method you think you will use most.

##### Step 3 Target Languages
Here you can choose the target languages you wish to translate your website into. To choose a language simply click the published button to select it. (You may see some text in red saying that the language does not have a created content record, but don’t worry just click “Fix it!”)

You can add as many languages as you like here, and you can add or remove languages from your dashboard after setup is complete. You can also select your default translation method for the chosen languages here and change them later.

If You don’t see the language you are looking for you can click on the add language button at the bottom of the page to get more languages. Once you can see all your target languages have been installed on screen click next to continue.

##### Step 4 backup your site.
To complete the setup, neno needs to move any existing translations to the neno database, to prevent having to translate content again. At this point we recommend that you backup your site as once the content is removed there is no way to undo this action. Once your site is backed up click on the proceed button to complete the installation.


## Components not fully compatible with Neno

### JReviews
This component is not fully compatible with Neno because it has a table called \#__jreviews_content which mixes content in several languages. That table contains fields to store tags in different languages such as tagesX (Spanish tags), tagenX (English tags), tagroX ( Romanian tags), tagdeX (German tags), tagitX (Italian tags). 

The correct method for implementing tags in Joomla is to use com_tags or create a separate table for tags and use the “language” field. 
