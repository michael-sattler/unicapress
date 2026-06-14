<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/functions-universal.php';

$pagetitle = 'About the Engine';
$pagdescription = 'How Unica Press prepares a telling — the machinery, plainly disclosed.';

ob_start();
?>
<div class="page-header text-center">
    <div class="container container-narrow">
        <span class="eyebrow">Honest machinery</span>
        <img src="/app/assets/images/icon_machine2.png" alt="Unica Press" class="img-fluid mb-3" style="max-height: 150px;">
        <h1>About the Engine</h1>
        <p class="lead text-muted mb-0">
            Every telling you can read through Unica Press is prepared by a machine.<br/>Here is exactly how, and what stands between it and you.
        </p>
    </div>
</div>

<div class="container container-narrow pb-5">
    <p>
        We say this plainly, on this page and on every manuscript's own colophon, because we think it
        matters: nothing a reader receives from Unica Press was written by a person, scene by scene,
        for them. It was prepared &ndash; composed, drafted, checked, and fixed &ndash; by a pipeline
        running against a world an author built. We are not interested in hiding that, dressing it up,
        or apologizing for it. It is the method, and the method is the point.
    </p>
    <p class="text-muted">
        What follows is the same disclosure in more detail, for anyone who wants to know what actually
        happens between the moment a reader requests a telling and the moment the first page appears.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

    <span class="eyebrow">Before a word is written</span>
    <h2>The story is decided before it is told</h2>
    <p>
        A world built for Unica Press is more than a setting. It is a <strong>World Package</strong>:
        the canon &ndash; places, people, history, the things that are true &ndash; a library of
        authored <strong>story shapes</strong> with their own beginnings, turns, and endings, a set of
        rules for how names in that world are formed, and a <strong>style fingerprint</strong> distilled
        from the author's own writing.
    </p>
    <p>
        When a telling is requested, the engine does not start improvising. It selects a story shape
        compatible with the request, populates its roles with characters named according to the
        world's own conventions, and composes a complete spine &ndash; every beat, every turn, the
        ending included &ndash; in a single pass, before a single sentence of prose exists. This spine
        is then fixed for the life of the telling. The press cannot change its mind about how a story
        ends partway through telling it, because by the time the first scene is written, it already
        doesn't have to.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

    <span class="eyebrow">Then, scene by scene</span>
    <h2>Prose is written against the spine, <br/>never instead of it</h2>
    <p>
        With the spine fixed, the engine writes one scene at a time &ndash; drawing on the world's
        invariants, the relevant location, the spine itself, and a running <strong>continuity
        ledger</strong> that tracks what the reader already knows, where everyone is, and what has
        already happened. The model's freedom is in the prose: rhythm, dialogue, description, the
        texture of the telling. The plot is not its to decide.
    </p>
    <p class="text-muted">
        While a reader is on one scene, the next is already being written and checked, so that turning
        the page rarely means waiting. This is a pacing detail, not a magic trick, and we mention it
        here for the same reason we mention everything else.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

    <span class="eyebrow">What stands between the model and you</span>
    <h2>An author's editorial battery, <br/>before you ever see a page</h2>
    <p>
        Every scene passes through a set of automated checks before it is offered to a reader:
    </p>
    <ul>
        <li>
            <strong>Canon check.</strong> Claims the scene makes about the world are checked against
            that world's authored canon. A scene that contradicts what the author has established is
            not shown to a reader as written.
        </li>
        <li>
            <strong>Continuity check.</strong> The scene is checked against the spine beat it is meant
            to fulfil and against the continuity ledger &ndash; the running record of time, place, and
            what has happened so far.
        </li>
        <li>
            <strong>Naming check.</strong> Any new name the scene introduces is checked against the
            world's naming conventions and its list of names already in use, so the world's onomastics
            stay consistent without ever repeating a name that means something elsewhere.
        </li>
        <li>
            <strong>Style score.</strong> The scene is scored against the author's style fingerprint
            &ndash; diction, rhythm, the constructions the author does and doesn't use. A scene that
            doesn't sound like the world it claims to be from is sent back.
        </li>
        <li>
            <strong>Content safety pass.</strong> Every scene is checked for age-appropriateness before
            it reaches a reader.
        </li>
    </ul>
    <p>
        A scene that fails any of these is regenerated, with the failure noted, up to a small number of
        times. If it still doesn't pass, the best candidate is used and the telling is flagged for the
        world's author to review &ndash; quietly, after the fact. A reader never sees a failed attempt,
        a retry, or a "regenerating&hellip;" spinner. What a reader sees has already passed.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

    <span class="eyebrow">Once read, fixed forever</span>
    <h2>Reading is publication</h2>
    <p>
        The moment a reader finishes a scene, that scene is written to permanent storage with a
        timestamp and an accession record, and it cannot be altered, regenerated, or replaced by any
        system &ndash; including ours &ndash; ever again. There is no "regenerate this," no "try
        again," no quiet edit after the fact. What you read is what was prepared, permanently, the
        moment you read it.
    </p>
    <p class="text-muted">
        Nothing a telling contains is written back into a world's canon. A telling is, by design,
        apocryphal &ndash; an edition of one, sitting outside the record &ndash; unless the world's
        author deliberately chooses, afterward, to bring some part of it in. That choice is always
        the author's, never the engine's, and never automatic.
    </p>

    <p class="fleuron my-5">&#10072; &#10070; &#10072;</p>

</div>
<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/../elements/layout.php';
