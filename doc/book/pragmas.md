# Pragmas

Pragmas are a way to extend the mustache syntax, as well as alter it. They are
invoked using the syntax `{{%PRAGMA-NAME}}`.

Pragmas can affect both the lexing (tokenization/parsing) and rendering phases
of templating. In the former case, they can be used to analyze tokens and
potentially change them; in the latter, they can be used to alter how view data
is processed and rendered.

> ## Pragma names
>
> Pragma names consist **only** of uppercase alphabetical characters and dashes.

## Behavior

### Pragmas Are Section Specific

Lets take the Implicit-Iterator defined in one section:

```html
{{!template-with-pragma-in-section.mustache}}
Some content, with {{type}}
{{#section1}}
{{%IMPLICIT-ITERATOR}}
    {{#subsection}}
        {{.}}
    {{/subsection}}
{{/section1}}
{{#section2}}
    {{#subsection}}
        {{.}}
    {{/subsection}}
{{/section2}}
```

Note that the pragma is only defined in `section1`.

Now consider the following view:

```php
$mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
$test = $mustache->render('template-with-pragma-in-section', [
    'type' => 'style',
    'section1' => [
        'subsection' => [1, 2, 3],
    ],
    'section2' => [
        'subsection' => [4, 5, 6],
    ],
]);
```

When the above is excecuted, only the contents of `section1.subsection` will be
iterated; `section2.subsection` will not. This results in the following output:

```html
Some content, with style

        1
            2
            3
```
                
### Pragmas Do Not Extend To Partials

Pragmas only apply to the specific template in which they are defined. This
means that any partials or parent templates (if using template inheritance) are
not affected.

As an example, consider the following templates:

```html
{{!partial-with-section.mustache}}
This is from the partial
{{#section}}
    {{#subsection}}
        {{.}}
    {{/subsection}}
{{/section}}

{{!template-with-pragma-and-partial.mustache}}
{{%IMPLICIT-ITERATOR}}
Some content, with {{type}}
{{>partial-with-section}}
```

And the following view:

```php
$mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
$test = $mustache->render('template-with-pragma-and-partial', [
    'type' => 'style',
    'section' => [
        'subsection' => [1, 2, 3],
    ],
]);
```

You can expect the following output:

```html
Some content, with style

This is from the partial
```

## Interface

Pragmas extend the behavior of Mustache, allowing users to opt-in to features
that exist outside the Mustache specification. phly-mustache provides
`Phly\Mustache\Pragma\PragmaInterface` to allow you to define code that
implements these features.

```php
namespace Phly\Mustache\Pragma;

use Phly\Mustache\Mustache;

interface PragmaInterface
{
    /**
     * Retrieve the name of the pragma
     *
     * @return string
     */
    public function getName();

    /**
     * Whether or not this pragma can handle the given token
     *
     * @param  int $token
     * @return bool
     */
    public function handlesToken($token);

    /**
     * Parse the provided token.
     *
     * If the pragma handles a given token, it is allowed to parse it; the
     * lexer will call this method when the token has been created, passing the
     * token struct.
     *
     * Token structs contain, minimally:
     *
     * - index 0: the token type (see the `Lexer::TOKEN_*` constants)
     * - index 1: the related data for the token
     *
     * The method MUST return a token struct on completion; if the pragma does
     * not need to do anything, it can simply `return $tokenStruct`.
     *
     * @param array $tokenStruct
     * @return array
     */
    public function parse(array $tokenStruct);

    /**
     * Render a given token.
     *
     * Returning an empty value returns control to the renderer.
     *
     * $tokenStruct is an array consisting minimally of:
     *
     * - 0: int token (from Lexer::TOKEN_* constants)
     * - 1: mixed data (data associated with the token)
     *
     * @param  array $tokenStruct
     * @param  mixed $view
     * @param  array $options
     * @param  Mustache $mustache Mustache instance handling rendering.
     * @return mixed
     */
    public function render(array $tokenStruct, $view, array $options, Mustache $mustache);
}
```

phly-mustache also provides a trait, `Phly\Mustache\Pragma\PragmaNameAndTokensTrait`,
that you can use to simplify pragma development. `use` the trait, and define the
properties `$name` and `$tokensHandled`, and you will only need to define the
`render()` method at that point.

```php
use Phly\Mustache\Lexer;
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\PragmaInterface;
use Phly\Mustache\Pragma\PragmaNameAndTokensTrait;

class FooBarPragma implements PragmaInterface
{
    use PragmaNameAndTokensTrait;

    private $name = 'FOO-BAR';

    private $tokensHandled = [
        Lexer::TOKEN_VARIABLE,
    ];

    public function parse(array $tokenStruct)
    {
        // ...
    }

    public function render(array $tokenStruct, $view, array $options, Mustache $mustache)
    {
        // ...
    }
}
```

## Using Pragmas

To use a pragma, you need to add an instance of it to the
`Phly\Mustache\Pragma\PragmaCollection` instance composed by the `Mustache`
instance. You can retrieve it by calling the `getPragmas()` method of the
`Mustache` instance:

```php
$pragmas = $mustache->getPragmas();
```

Once retrieved, you can perform the following operations:

- `add(PragmaInterface $pragma)` will attach a pragma, exposing it to templates.
  If the pragma has already been added, or another pragma with the same name
  has, it will raise an exception.
- `has($pragma)` will tell you if a pragma with a given name is present in the
  collection.
- `get($pragma)` will retrieve a pragma with a given name from the collection.
  If the pragma is not present in the collection, it will raise an exception.
- `clear()` will remove all pragmas from the collection.

The collection is countable and iterable.

## Shipped Pragmas

### Implicit Iterator

Normally, sections expect key/value pairs, but often when iterating, you will
have simply a list of values. 

The implicit iterator pragma allows iteration of indexed arrays or `Traversable`
objects with scalar values, with the option of specifying the iterator "key" to
use within the template. By default, a variable key "." will be replaced by the
current value of the iterator.

To assign the name, add the verbiage ` iterator=varname` when invoking the
pragma.

As an example:

```html
{{!template-with-implicit-iterator.mustache}}
{{%IMPLICIT-ITERATOR iterator=bob}}
{{#foo}}
    {{bob}}
{{/foo}}
```

The above will assign each list item in `foo` to the variable `bob`, which we
can then render.

Here's the related view:
    
```php 
$mustache->getRenderer()->addPragma(new Phly\Mustache\Pragma\ImplicitIterator());
$view = ['foo' => [1, 2, 3, 4, 5, 'french']];
$test = $mustache->render(
    'template-with-implicit-iterator',
    $view
);
```

The two together render the following:

```html
1
2
3
4
5
french
```

We could have also used the default `.` placeholder instead, which would have
resulted in the following template:

```html
{{!template-with-implicit-iterator.mustache}}
{{%IMPLICIT-ITERATOR}}
{{#foo}}
    {{.}}
{{/foo}}
```

### Contextual Escape

The CONTEXTUAL-ESCAPE pragma allows you to specify an escaping context when
specifying a variable in your template. Contexts include:

- html (default; you need not specify this)
- attr (for escaping HTML attribute values)
- js (for escaping JavaScript)
- css (for escaping CSS)
- url (for escaping URLs)

This allows the following:

```html
{{%CONTEXTUAL-ESCAPE}}
<html>
<head>
   <script>{{scripts|js}}</script>
   <style>{{styles|css}}</script>
</head>
<body>
    <article class="{{article_class|attr}}">
        <a href="{{article_url|url}}">link</a>
    </article>
</body>
</html>
```

In order to use the pragma, you must first register it with `Mustache`:

```php
use Phly\Mustache\Pragma\ContextualEscape;

$mustache->getPragmas()->add(new ContextualEscape());
```

### Sub-Views

The SUB-VIEWS pragma allows you to implement the two-step view pattern.  When
active, any variable whose value is an instance of
`Phly\Mustache\Pragma\SubView` will be substituted by rendering the template and
view that object encapsulates.

The `SubView` class takes a template name and a view via the constructor:

```php
use Phly\Mustache\Pragma\SubView;

$subView = new SubView('some-partial', ['name' => 'Matthew']);
```

That object is then assigned as a value to a view key:

```php
$view = new stdClass;
$view->content = $subView;
```

The template might look like this:

```html
{{!layout}}
{{%SUB-VIEWS}}
<html>
<body>
    {{content}}
</body>
</html>
```

and the partial like this:

```html
{{!some-partial}}
Hello, {{name}}!
```

Rendering the view:

```php
use Phly\Mustache\Mustache;
use Phly\Mustache\Pragma\SubViews;

$mustache = new Mustache();
$subViews = new SubViews($mustache);
$rendered = $mustache->render('layout', $view);
```

will result in:

```html
<html>
<body>
    Hello, Matthew!
</body>
</html>
```

Sub views may be nested, and re-used.

Typically, you should use [template inheritance](syntax.md#placeholders-and-template-inheritance)
instead whenever possible, as it is built-in, and easier to re-use.
