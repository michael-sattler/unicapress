<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/functions-universal.php';
require_once __DIR__ . '/includes/functions-waitlist.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['waitlist_submit'])) {
    $result = add_waitlist_signup(
        $_POST['waitlist_name'] ?? '',
        $_POST['waitlist_email'] ?? '',
        'home'
    );

    if ($result['success']) {
        $_SESSION['waitlist_toast'] = $result['message'];
        header('Location: /');
        exit;
    }

    $_SESSION['error_message'] = $result['message'];
    $_SESSION['waitlist_form_name'] = trim((string) ($_POST['waitlist_name'] ?? ''));
    $_SESSION['waitlist_form_email'] = trim((string) ($_POST['waitlist_email'] ?? ''));

    header('Location: /#waitlist');
    exit;
}

$waitlist_name = $_SESSION['waitlist_form_name'] ?? '';
$waitlist_email = $_SESSION['waitlist_form_email'] ?? '';
unset($_SESSION['waitlist_form_name'], $_SESSION['waitlist_form_email']);

$pagetitle = SITE_NAME;
$pagdescription = SITE_TAGLINE;

ob_start();
?>

<!-- ============================== HERO ============================== -->
<section class="stripe stripe-paper">
    <div class="container text-center py-3 py-md-5">
        <img src="/app/assets/images/stamp_1.png" alt="Unica Press" class="img-fluid mb-3" style="max-height: 100px;">
        <h1 class="hero-title ink-set mb-3">One author's world.<br>One-of-a-kind stories.</h1>
        <hr class="rule-accent mx-auto">
        <p class="hero-lead mx-auto mb-4">
            Unica Press is a publishing house that prints stories which exist only once, for one reader.
            A worldbuilder authors a canon, a voice, a way of telling &ndash; and the press prepares an
            edition: a complete story, set once, fixed the moment it is read, and never produced again.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a class="btn btn-accent btn-lg disabled" href="<?php echo htmlspecialchars(APP_ARCHIVE_URL); ?>" target="_blank" rel="noopener noreferrer">
                Visit the Grand Archive
            </a>
            <a class="btn btn-outline-dark btn-lg" href="/about">
                What is Unica Press?
            </a>
        </div>
    </div>
</section>

<!-- ========================= MANIFESTO STRIPE ======================== -->
<section class="stripe stripe-ink text-center">
    <div class="container py-3">
        <img src="/app/assets/images/typeblock_1-white+trans.png" alt="Unica Press" class="img-fluid mb-3" style="max-height: 100px;">
        <p class="lead mx-auto mb-0" style="max-width: 38rem;">
            "Every copy the only copy." Scarcity, here, is not a supply limit &ndash; it is the simple
            fact that a story prepared for one reader belongs to that reader alone, and to no one
            else, ever again.
        </p>
    </div>
</section>

<!-- ========================== HOW IT WORKS =========================== -->
<section class="stripe stripe-paper">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="eyebrow">How an edition is prepared</span>
            <h2 class="mb-0">From a world, a single story</h2>
        </div>
        <div class="row gy-4">
            <div class="col-md-4">
                <h3 class="h5">I. The world is authored</h3>
                <p class="text-muted mb-0">
                    A worldbuilder sets down a world's canon, its places and people, its story shapes,
                    and the voice in which it is told. This is the press's only raw material &ndash;
                    there is no improvisation outside it.
                </p>
            </div>
            <div class="col-md-4 col-rule">
                <h3 class="h5">II. A telling is composed</h3>
                <p class="text-muted mb-0">
                    From a reader's small set of preferences, the press composes a complete spine
                    &ndash; a beginning, a shape, and an ending &ndash; before a single word of prose
                    is written. The story is decided before it is told.
                </p>
            </div>
            <div class="col-md-4 col-rule">
                <h3 class="h5">III. The telling is fixed</h3>
                <p class="text-muted mb-0">
                    Each scene is set down as the reader reaches it, and cannot be altered, regenerated,
                    or read again as anything other than what it became. Reading is publication.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ========================= FOR WORLDBUILDERS ======================== -->
<section class="stripe stripe-paper stripe-rule-top">
    <div class="container py-3">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <span class="eyebrow">For worldbuilders</span>
                <h2 class="mb-3">Every world holds more stories than its author can write</h2>
                <p class="mb-3">
                    You've already done the hard part. The world exists &ndash; the places, the people,
                    the histories that almost happened and the ones that did, the voice the whole thing
                    is told in. Most of it will never become a finished story, simply because one author
                    only has so many hours.
                </p>
                <p class="text-muted mb-3">
                    Unica Press is a second printing house for that surplus. Give it your canon, your
                    story shapes, and the fingerprint of your prose, and it will prepare short, complete
                    stories &ndash; each one written for a single reader, in your voice, true to your
                    world &ndash; without asking you to write a word of them yourself.
                </p>
                <p class="mb-3 d-none">
                    Nothing it writes comes back to haunt your canon. Every telling is apocryphal until
                    <em>you</em> say otherwise &ndash; the record stays exactly as you left it.
                </p>
                <p class="mb-3">
                    Your readers play a unique role in your storytelling journey, helping shape your world like humans do in real life. <strong>A bard never sang the same song twice, but always honored the saga.</strong>
                </p>
                <a href="/contact" class="btn btn-accent">
                    Build your world <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="col-lg-5">
                <div class="ps-lg-4">
                    <div class="pb-3 mb-3 d-flex align-items-start" style="border-bottom: 1px solid var(--up-rule);">
                        <img src="/app/assets/images/icon_world.png" alt="World" class="img-fluid mb-3 float-start" style="max-height: 100px;">
                        <div class="ms-4">
                            <h4 class="mb-1 text-oxblood">Your world</h4>
                            <p class="text-muted small mb-0">
                                Tellings derive from your world; they never write back to it ... unless you say so. Canonizing
                                anything is a deliberate act &ndash; only you can do it.
                            </p>
                        </div>
                    </div>
                    <div class="pb-3 mb-3 d-flex align-items-start" style="border-bottom: 1px solid var(--up-rule);">
                        <img src="/app/assets/images/icon_typewriter.png" alt="World" class="img-fluid mb-3 float-start" style="max-height: 100px;">
                        <div class="ms-4">
                            <h4 class="mb-1 text-oxblood">Your authorial voice</h4>
                            <p class="text-muted small mb-0">
                                The press learns the rhythm, diction, and habits of your prose, and uses it to score
                                every scene against it before a reader ever sees it.
                            </p>
                        </div>
                    </div>
                    <div class="pb-3 mb-3 d-flex align-items-start" ">
                        <img src="/app/assets/images/icon_reader.png" alt="World" class="img-fluid mb-3 float-start" style="max-height: 100px;">
                        <div class="ms-4">  
                            <h4 class="mb-1 text-oxblood">Your readers</h4>
                            <p class="text-muted small mb-0">
                                Each reader's unique edition can enrich the background tapestry. People and places that emerge from tellings can recur in future tellings, if you choose to include them.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================= WORKBENCH / WORLDBUILDER PROMOTION =========================== -->
<section class="stripe stripe-workbench">
    <div class="container py-3">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <span class="eyebrow">Introducing the Unica Press Workbench</span>
                <h2 class="mb-3">A place  for everything in your head to finally live</h2>
                <p class="mb-3">
                    Most of a world never makes it past a notebook margin or a half-finished doc &ndash;
                    a face you can picture but haven't named, a place you've never described, a thread you
                    keep meaning to follow. The Unica Press Workbench is a canvas built to hold all of it at once:
                    every place, person, object, and idea you imagine, set down the moment you think of it
                    and linked to everything it touches.
                </p>
                <p class="text-muted mb-4">
                    Your Workbench is the foundation for everything that comes after: the stories, the reference bibles, the AI context, the brainstorming. If it's all captured, you can build anything.
                </p>

                <p class="text-muted mb-4 d-none">
                    Nothing has to be finished to belong here. Drop a thought down as a draft, let it sit
                    beside the rest of your canon, and watch the connections between them surface on their
                    own as the world fills in &ndash; nothing is fixed until you say it is.
                </p>
                <a href="https://worldbuilder.unicapress.com" class="btn btn-wb-accent">
                    Open the workbench <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="col-lg-6">
                <div class="wb-promo-canvas">
                    <div class="wb-promo-card" style="--type-color:#6b9fb8;">
                        <div class="wb-promo-card-type"><i class="ti ti-map-pin"></i> Location</div>
                        <div class="wb-promo-card-name">The Salt Quarter</div>
                        <div class="wb-promo-card-canon canon">Canon</div>
                    </div>
                    <div class="wb-promo-card" style="--type-color:#a87caa;">
                        <div class="wb-promo-card-type"><i class="ti ti-user"></i> Character</div>
                        <div class="wb-promo-card-name">Imeril Voss</div>
                        <div class="wb-promo-card-canon canon">Canon</div>
                    </div>
                    <div class="wb-promo-card" style="--type-color:#b8936b;">
                        <div class="wb-promo-card-type"><i class="ti ti-box"></i> Object</div>
                        <div class="wb-promo-card-name">The Brass Ledger</div>
                        <div class="wb-promo-card-canon draft">Draft</div>
                    </div>
                    <div class="wb-promo-card" style="--type-color:#8c9eb8;">
                        <div class="wb-promo-card-type"><i class="ti ti-feather"></i> Theme</div>
                        <div class="wb-promo-card-name">Debts that outlive their debtors</div>
                        <div class="wb-promo-card-canon canon">Canon</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================= FLAGSHIP WORLD =========================== -->
<section class="stripe stripe-deep d-none">
    <div class="container py-3">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <span class="eyebrow">The flagship world</span>
                <h2 class="mb-3">The Grand Archive of the Steamlands</h2>
                <p class="mb-3">
                    Patrons of the Archive present a request slip to a Victorian difference engine,
                    watch it retrieve a manuscript from the stacks, and read a story that has been
                    prepared for them &ndash; and for no one else who will ever live.
                </p>
                <p class="text-muted mb-4">
                    The Steamlands is the first world built on the Unica Press engine. Its canon, its
                    registers, and its reading room have their own character; the press itself remains
                    the quiet machinery behind every world it serves.
                </p>
                <a class="btn btn-accent disabled" href="<?php echo htmlspecialchars(APP_ARCHIVE_URL); ?>" target="_blank" rel="noopener noreferrer">
                    Visit the Grand Archive
                </a>
            </div>
            <div class="col-lg-5 text-center">
                <img src="/app/assets/images/logo_simple-black+trans.png" alt="Grand Archive" class="img-fluid" style="max-height: 80px;">
            </div>
        </div>
    </div>
</section>

<!-- ============================ DISCLOSURE ============================ -->
<section class="stripe stripe-paper text-center">
    <div class="container py-3">
        <p class="fleuron mb-4">&#10072; &#10070; &#10072;</p>
        <span class="eyebrow">Honest machinery</span>
        <h2 class="mb-3">No concealment, anywhere</h2>
        <p class="mx-auto mb-3" style="max-width: 38rem;">
            Every telling is prepared by a machine, working from a world built by a human author,
            for a single reader. We say so plainly, on every page where it matters &ndash; disclosure
            is not a footnote here. It is part of the craft.
        </p>
        
        <a href="/about-the-engine" class="btn btn-accent-outline">
            Read about the engine <i class="fa-solid fa-arrow-right-long ms-1"></i>
        </a>
    </div>
</section>

<!-- ============================== CLOSING ============================= -->
<section class="stripe stripe-ink text-center">
    <div class="container py-4">
        <p class="lead mb-1">Every copy the only copy.</p>
        <p class="small mb-0" style="opacity: 0.65;">&ndash; Unica Press</p>
    </div>
</section>
<!-- ============================== WAITLIST ============================== -->
<section class="stripe stripe-paper text-center" id="waitlist">
    <div class="container py-3">
        <p class="fleuron mb-4">&#10072; &#10070; &#10072;</p>
        <span class="eyebrow">Learn more</span>
        <h2 class="mb-3">Get updates when we're ready for testing</h2>
        <p class="mx-auto mb-4" style="max-width: 38rem;">
            If you're a worldbuilder interested in the next generation of storytelling, join the waitlist
            and we'll reach out when early testing opens.
        </p>

        <button
            type="button"
            class="btn btn-accent-outline waitlist-toggle"
            data-bs-toggle="collapse"
            data-bs-target="#waitlist-form"
            aria-expanded="false"
            aria-controls="waitlist-form"
        >
            Sign up for updates <i class="fa-solid fa-envelope ms-1"></i>
        </button>

        <div class="collapse waitlist-panel mx-auto mt-4 col-lg-6 offset-lg-3" id="waitlist-form">
            <form method="post" action="/#waitlist" class="waitlist-form text-start">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="waitlist_name" class="form-label">Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="waitlist_name"
                            name="waitlist_name"
                            value="<?php echo htmlspecialchars($waitlist_name); ?>"
                            maxlength="255"
                            autocomplete="name"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="waitlist_email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="waitlist_email"
                            name="waitlist_email"
                            value="<?php echo htmlspecialchars($waitlist_email); ?>"
                            maxlength="255"
                            autocomplete="email"
                            required
                        >
                    </div>
                    <div class="col-12">
                        <button type="submit" name="waitlist_submit" value="1" class="btn btn-accent w-100">
                            Join the waitlist <i class="fa-solid fa-pen-nib ms-1"></i>
                        </button>
                        <p class="waitlist-form-note mb-0 mt-3 text-center">
                            One note when testing opens. No spam, no resale — just an invitation.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
$page_content = ob_get_clean();
require_once __DIR__ . '/elements/layout.php';
