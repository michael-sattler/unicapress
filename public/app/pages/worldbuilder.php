<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/functions-universal.php';

$pagetitle = 'About';
$pagdescription = 'What Unica Press is, and why it exists.';

ob_start();
?>
<div class="page-header text-center">
    <div class="container container-narrow">
        <span class="eyebrow">Worldbuilder</span>
        <img src="/app/assets/images/icon_world.png" alt="Unica Press" class="img-fluid mb-3" style="max-height: 150px;">
        <h1>Tools to build worlds</h1>
        <p class="lead text-muted mb-0">
            
        </p>
    </div>
</div>

<div class="container container-narrow pb-5">
    <span class="eyebrow">Why this exists</span>
    <h2>An author's vision, past the limits of pen and hours</h2>
    <p>
        Unica Press began as a question: what happens to an author's intent &ndash; their world, their
        voice, the way they tell a story &ndash; when it is filtered through the increasing capability
        of large language models? Not whether a machine can write, but whether it can conjure and extend a world <i>that a particular someone built</i>.
    </p>
    <p>
        The point is never to replace the author. It's to let an author's vision extend beyond what
        pen, ink, type, and the hours in a day will ever allow one person to produce. A world can hold
        a thousand stories its author will never have time to write. We think that surplus is worth
        exploring.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

</div>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/../elements/layout.php';
