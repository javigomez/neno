---
layout: index
---
### Welcome to the Neno Documentation

```
The documentation is not ready yet. Please be patient!
```



<a name="translating-special-content-in-joomla"></a>
## Translating special content in Joomla
When translating Joomla content there are some special tags, variables and characters to look out for. 

### Backslashes
If the source text contains a backslashes it is important to include them in the translation as well. Typical backslashes may be `\’` or `\”` as well as `\\`. It is important to maintain the same spacing around backslashes as the source text.

### Double quotes
If the source string contains double quotes `“` then it is OK to also use them in the translated text. If the source language does not contain double quotes but are needed in the translation then please use the HTML entity `&quot;` 

### Single quotes
If the source string contains single quotes `‘` then it is OK to also use them in the translated text. If the source language does not contain single quotes but are needed in the translation then please use the HTML entity `&#39;`

### %s and other % variables
If a source string contains a `%` sign followed by a character it is a variable and will be replaced by something else before being displayed. This list shows what the `%` variable will be replaced with.

* `%s` a variable that will be replaced by a string
* `%d` a variable that will be replaced by a number
* `%1$s` a variable that will be replaced by a string using a numbered reference

If a string contains more than one `%s` variable the replacements of the variable will be in the same order as the source string. Numbered variables such as `%1` and `%2` can be ordered differently.
### HTML
HTML tags are all surrounded by a start character `<` and an end character `>`. Anything between these tags should not be translated. Some HTML tags have an opening tag and a closing tag. Closing tags are preceded with a slash `/` such as for instance an anchor (link):
```
<a href=”http://google.com”>Translate this text</a>
```
Notice that the opening tag is an `<a>` tag and that the closing tag is a `</a>` tag. Content between an opening and closing tag should be translated. 


## Components not fully compatible with Neno

. 
