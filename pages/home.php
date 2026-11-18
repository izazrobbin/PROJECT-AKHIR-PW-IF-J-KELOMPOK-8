<?php

/*
|--------------------------------------------------------------------------
| MENGAMBIL LOWONGAN TERBARU
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        jobs.id,
        jobs.title,
        jobs.location_type,
        jobs.location_name,
        jobs.employment_type,
        jobs.description,
        jobs.deadline,
        departments.name AS department_name

    FROM jobs

    INNER JOIN departments
        ON jobs.department_id = departments.id

    WHERE jobs.status = 'published'

    AND (
        jobs.deadline IS NULL
        OR jobs.deadline >= CURDATE()
    )

    ORDER BY jobs.published_at DESC, jobs.created_at DESC

    LIMIT 6
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$latestJobs = $stmt->fetchAll();

?>

<!-- HERO SECTION -->

<section class="hero-section">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7">

                <span class="hero-label">

                    <i class="bi bi-briefcase-fill"></i>

                    ARTANA CORPORATION CAREER PORTAL

                </span>

                <h1 class="hero-title">

                    Find Your Future.
                    <span>Build Your Career.</span>

                </h1>

                <p class="hero-description">

                    Temukan peluang karier terbaik bersama
                    Artana Corporation. Mulai perjalanan
                    profesionalmu dan kembangkan potensimu
                    bersama kami.

                </p>

                <div class="hero-buttons">

                    <a
                        href="index.php?page=jobs"
                        class="btn btn-primary-custom"
                    >

                        Explore Jobs

                        <i class="bi bi-arrow-right"></i>

                    </a>

                    <a
                        href="index.php?page=register"
                        class="btn btn-outline-custom"
                    >

                        Join Us

                    </a>

                </div>

            </div>

            <div class="col-lg-5">

                <div class="hero-card">

                    <div class="hero-card-icon">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <h3>Grow With Us</h3>

                    <p>

                        Your next opportunity starts here.
                        Explore available positions and
                        take the next step in your career.

                    </p>

                    <div class="hero-card-bottom">

                        <i class="bi bi-check-circle-fill"></i>

                        Career Opportunities

                    </div>

                    <div class="hero-card-bottom">

                        <i class="bi bi-check-circle-fill"></i>

                        Professional Development

                    </div>

                    <div class="hero-card-bottom">

                        <i class="bi bi-check-circle-fill"></i>

                        Build Your Future

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- SEARCH SECTION -->

<section class="search-section">

    <div class="container">

        <div class="search-box">

            <form action="index.php"
                  method="GET">

                <input
                    type="hidden"
                    name="page"
                    value="jobs"
                >

                <div class="row g-3 align-items-center">

                    <div class="col-lg-9">

                        <div class="search-input">

                            <i class="bi bi-search"></i>

                            <input
                                type="text"
                                name="q"
                                placeholder="Search job title or department..."
                            >

                        </div>

                    </div>

                    <div class="col-lg-3">

                        <button
                            type="submit"
                            class="btn btn-primary-custom w-100"
                        >

                            Search Jobs

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</section>


<!-- LATEST JOBS -->

<section class="jobs-section">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-label">
                    CAREER OPPORTUNITIES
                </span>

                <h2>Latest Job Opportunities</h2>

                <p>
                    Temukan pekerjaan yang sesuai
                    dengan kemampuan dan minatmu.
                </p>

            </div>

            <a
                href="index.php?page=jobs"
                class="view-all-link"
            >

                View All Jobs

                <i class="bi bi-arrow-right"></i>

            </a>

        </div>


        <div class="row g-4">

            <?php if (count($latestJobs) > 0): ?>

                <?php foreach ($latestJobs as $job): ?>

                    <div class="col-lg-4 col-md-6">

                        <div class="job-card">

                            <div class="job-card-top">

                                <div class="job-icon">

                                    <i class="bi bi-building"></i>

                                </div>

                                <span class="job-type">

                                    <?= htmlspecialchars(
                                        $job['employment_type'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </div>

                            <h4>

                                <?= htmlspecialchars(
                                    $job['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </h4>

                            <p class="job-department">

                                <?= htmlspecialchars(
                                    $job['department_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>

                            <div class="job-information">

                                <span>

                                    <i class="bi bi-geo-alt"></i>

                                    <?= htmlspecialchars(
                                        $job['location_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                                <span>

                                    <i class="bi bi-laptop"></i>

                                    <?= htmlspecialchars(
                                        $job['location_type'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </div>

                            <div class="job-card-footer">

                                <a
                                    href="index.php?page=job_detail&id=<?= (int) $job['id'] ?>"
                                    class="btn btn-job-detail"
                                >

                                    View Details

                                    <i class="bi bi-arrow-right"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="empty-state">

                        <i class="bi bi-briefcase"></i>

                        <h4>No Jobs Available Yet</h4>

                        <p>

                            Saat ini belum ada lowongan
                            pekerjaan yang dipublikasikan.

                            Silakan kunjungi kembali
                            halaman ini nanti.

                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- WHY ARTANA -->

<section class="why-section">

    <div class="container">

        <div class="text-center section-heading-center">

            <span class="section-label">
                WHY ARTANA
            </span>

            <h2>Build Your Career With Us</h2>

            <p>
                Kesempatan untuk berkembang dan
                membangun masa depan profesional.
            </p>

        </div>

        <div class="row g-4 mt-3">

            <div class="col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>

                    <h4>Career Growth</h4>

                    <p>

                        Kembangkan kemampuan dan
                        potensi profesional melalui
                        peluang karier yang tersedia.

                    </p>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-people"></i>

                    </div>

                    <h4>Professional Team</h4>

                    <p>

                        Bangun pengalaman dan
                        kolaborasi dalam lingkungan
                        kerja yang profesional.

                    </p>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-lightbulb"></i>

                    </div>

                    <h4>New Opportunities</h4>

                    <p>

                        Temukan peluang baru yang
                        dapat membantu perjalanan
                        kariermu.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>