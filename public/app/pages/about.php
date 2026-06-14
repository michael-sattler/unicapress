<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/functions-universal.php';

$pagetitle = 'About';
$pagdescription = 'What Unica Press is, and why it exists.';

ob_start();
?>
<div class="page-header text-center">
    <div class="container container-narrow">
        <span class="eyebrow">A publishing machine for worlds</span>
        <img src="/app/assets/images/icon_press2.png" alt="Unica Press" class="img-fluid mb-3" style="max-height: 150px;">
        <h1>About Unica Press</h1>
        <p class="lead text-muted mb-0">
            Unica Press is an engine that prepares unique, single-edition stories &ndash; tellings &ndash;
            set in an authored world. The reader does not control the plot; the world tells the story.
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

    <span class="eyebrow">On collaboration</span>
    <h2>Authorship has always been a collaboration</h2>
    <p>
        The credit on a screenplay or a television series is rarely the work of one mind. It is the
        product of a writers' room &ndash; producers, junior writers, actors, editors &ndash; each
        adding something, all of it shaped toward a single vision. The synthesis of many hands, under
        one editorial authority, routinely produces work beyond what any one of those hands could make
        alone.
    </p>
    <p>
        We think a language model can take a seat at that table. Not as an author, and not as a
        replacement for one, but as a collaborator &ndash; one capable of producing prose, scene after
        scene, in service of a vision that remains entirely the author's own. We don't think that needs
        an apology. We think it's a genuinely new kind of writers' room, and we find that exciting.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

    <span class="eyebrow">Within the author's confines</span>
    <h2>The model writes prose. The author writes the world.</h2>
    <p>
        We do not let the engine set its own terms. It does not invent canon, decide what a world's
        history contains, or choose how a story ends &ndash; those remain the author's, fully and only.
        What the engine does is operate strictly inside the boundaries an author has already drawn:
        the world's facts, its story shapes, its naming conventions, the rhythm and habit of its prose.
    </p>
    <p class="text-muted">
        The mechanics of how that boundary is enforced &ndash; the editorial battery every scene passes
        before a reader sees it &ndash; are laid out in full on
        <a href="/about-the-engine">About the Engine</a>. We'd rather over-explain than leave anyone
        guessing.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

    <span class="eyebrow">The flagship world</span>
    <h2>The Grand Archive of the Steamlands</h2>
    <p>
        The first world built on Unica Press is the Steamlands, delivered through
        <strong>The Grand Archive</strong>: a reading room where patrons present a request slip to a
        Victorian difference engine, and receive in return a manuscript prepared for them alone &ndash;
        a story that, once read, will exist for no one else who will ever live.
    </p>
    <p class="mb-0">
        <a class="btn btn-accent disabled" href="<?php echo htmlspecialchars(APP_ARCHIVE_URL); ?>" target="_blank" rel="noopener noreferrer">
            Visit the Grand Archive
        </a>
    </p>
</div>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/../elements/layout.php';
