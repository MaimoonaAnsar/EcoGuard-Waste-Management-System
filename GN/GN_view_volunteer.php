
<?php
session_start();

include __DIR__ . '/../includes/db.php';

// =========================================================
// GN ACCESS ONLY
// =========================================================

if (!isset($_SESSION['user_id']) || (int)$_SESSION['role_id'] !== 5) {
    header("Location: ../login.php");
    exit();
}


// =========================================================
// GET VOLUNTEER EVENTS
// =========================================================
// This page is ONLY for viewing volunteer participation.
// Certificate information is NOT included.

$stmt = $pdo->prepare("
    SELECT
        e.Event_Id,
        e.Name,
        e.Date,
        e.Location,
        COUNT(p.U_Id) AS Participant_Count
    FROM volunteer_event e
    LEFT JOIN user_participate_volunteer_event p
        ON p.Event_Id = e.Event_Id
    GROUP BY
        e.Event_Id,
        e.Name,
        e.Date,
        e.Location
    ORDER BY e.Date DESC
");

$stmt->execute();

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EcoGuard | Volunteer Events</title>


    <!-- Global responsive CSS -->
    <link rel="stylesheet" href="../css/ecoguard_responsive.css">


    <!-- =====================================================
         PAGE CSS
         Everything for this page is below
         ===================================================== -->

    <style>

        /* =====================================================
           MAIN PAGE
           ===================================================== */

        .gn-events-page {
            width: min(1200px, calc(100% - 40px));
            margin: 0 auto;
            padding: 40px 0 60px;
        }


        /* =====================================================
           PAGE HEADER
           ===================================================== */

        .page-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 30px;

            margin-bottom: 30px;
        }


        .heading-content {
            min-width: 0;
        }


        .eyebrow {
            display: inline-block;

            margin-bottom: 8px;

            color: #5b8061;

            font-size: 13px;
            font-weight: 700;

            letter-spacing: 0.08em;
            text-transform: uppercase;
        }


        .page-heading h1 {
            margin: 0 0 8px;

            color: #263b2b;

            font-size: clamp(28px, 4vw, 38px);

            line-height: 1.2;
        }


        .page-heading p {
            margin: 0;

            color: #6d756e;

            font-size: 15px;
        }


        /* =====================================================
           DASHBOARD BUTTON
           ===================================================== */

        .dashboard-link {
            display: inline-flex !important;

            align-items: center;
            justify-content: center;

            gap: 8px;

            width: auto !important;
            min-width: 0;

            height: 40px;

            padding: 0 16px;

            box-sizing: border-box;

            background: #eef4ed !important;

            border: 1px solid #d8e5d6 !important;

            border-radius: 8px;

            color: #3f7048 !important;

            text-decoration: none !important;

            font-size: 13px;
            font-weight: 700;

            white-space: nowrap;

            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }


        .dashboard-link span {
            font-size: 16px;
            line-height: 1;
        }


        .dashboard-link:hover {
            background: #dfeade !important;

            border-color: #c7d9c5 !important;

            color: #315c39 !important;

            transform: translateX(-2px);
        }


        /* =====================================================
           TABLE CONTAINER
           ===================================================== */

        .events-table-container {
            width: 100%;

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 4px 18px rgba(35, 58, 40, 0.05);
        }


        /* =====================================================
           TABLE HEADER
           ===================================================== */

        .table-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            padding: 22px 24px;

            border-bottom: 1px solid #e5e9e4;
        }


        .table-header h2 {
            margin: 0 0 4px;

            color: #293c2d;

            font-size: 19px;
        }


        .table-header p {
            margin: 0;

            color: #7a827b;

            font-size: 13px;
        }


        /* =====================================================
           TABLE WRAPPER
           ===================================================== */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;
        }


        /* =====================================================
           TABLE
           ===================================================== */

        .events-table {
            width: 100%;

            min-width: 850px;

            border-collapse: collapse;

            text-align: left;
        }


        .events-table thead {
            background: #f7f9f6;
        }


        .events-table th {
            padding: 14px 20px;

            border-bottom: 1px solid #e1e7e0;

            color: #687168;

            font-size: 12px;
            font-weight: 700;

            letter-spacing: 0.04em;

            text-transform: uppercase;

            white-space: nowrap;
        }


        .events-table td {
            padding: 18px 20px;

            border-bottom: 1px solid #edf0ec;

            color: #414a43;

            font-size: 14px;

            vertical-align: middle;
        }


        .events-table tbody tr:last-child td {
            border-bottom: none;
        }


        .events-table tbody tr {
            transition: background 0.15s ease;
        }


        .events-table tbody tr:hover {
            background: #fafcf9;
        }


        /* =====================================================
           EVENT NAME
           ===================================================== */

        .event-name {
            display: flex;

            align-items: center;

            gap: 13px;

            min-width: 210px;
        }


        .event-icon {
            display: flex;

            align-items: center;
            justify-content: center;

            flex: 0 0 40px;

            width: 40px;
            height: 40px;

            border-radius: 9px;

            background: #edf5ed;

            font-size: 19px;
        }


        .event-name strong {
            display: block;

            margin-bottom: 4px;

            color: #2d3d30;

            font-size: 14px;

            font-weight: 700;
        }


        .event-name span {
            display: block;

            color: #8a918b;

            font-size: 12px;
        }


        /* =====================================================
           TABLE INFORMATION
           ===================================================== */

        .table-info {
            display: flex;

            align-items: center;

            gap: 8px;

            white-space: nowrap;
        }


        .info-icon {
            font-size: 14px;
        }


        /* =====================================================
           VOLUNTEER BADGE
           ===================================================== */

        .volunteer-badge {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 7px 10px;

            border-radius: 7px;

            background: #f1f6f1;

            color: #3f6547;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =====================================================
           VIEW VOLUNTEERS BUTTON
           ===================================================== */

        .view-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 8px;

            padding: 9px 14px;

            border-radius: 7px;

            background: #3f7048;

            color: #ffffff;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .view-btn:hover {
            background: #315c39;

            transform: translateY(-1px);
        }


        .view-btn span {
            font-size: 15px;
        }


        /* =====================================================
           EMPTY STATE
           ===================================================== */

        .empty-state {
            display: flex;

            flex-direction: column;

            align-items: center;
            justify-content: center;

            min-height: 360px;

            padding: 40px;

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            text-align: center;
        }


        .empty-icon {
            display: flex;

            align-items: center;
            justify-content: center;

            width: 65px;
            height: 65px;

            margin-bottom: 18px;

            border-radius: 50%;

            background: #edf5ed;

            font-size: 28px;
        }


        .empty-state h2 {
            margin: 0 0 8px;

            color: #2d3d30;

            font-size: 21px;
        }


        .empty-state p {
            margin: 0 0 22px;

            color: #747c75;

            font-size: 14px;
        }


        /* =====================================================
           TABLET
           ===================================================== */

        @media (max-width: 768px) {

            .gn-events-page {
                width: calc(100% - 24px);

                padding: 25px 0 40px;
            }


            .page-heading {
                align-items: flex-start;

                flex-direction: column;

                gap: 18px;
            }


            .page-heading h1 {
                font-size: 28px;
            }


            /*
             * Dashboard stays compact.
             * It does NOT become full width.
             */

            .page-heading .dashboard-link {
                width: auto !important;

                min-width: 0;
            }


            .events-table-container {
                border-radius: 10px;
            }


            .table-header {
                padding: 18px;
            }


            /*
             * Keep the table readable on phones.
             * User can scroll horizontally.
             */

            .events-table {
                min-width: 760px;
            }


            .events-table th,
            .events-table td {
                padding: 14px 16px;
            }

        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media (max-width: 480px) {

            .gn-events-page {
                width: calc(100% - 20px);
            }


            .page-heading h1 {
                font-size: 25px;
            }


            .page-heading p {
                font-size: 14px;
            }


            .table-header h2 {
                font-size: 17px;
            }


            .dashboard-link {
                height: 38px;

                padding: 0 14px;

                font-size: 12px;
            }

        }

    </style>

</head>


<body>


<?php include 'gn_header.php'; ?>


<main class="gn-events-page">


    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <section class="page-heading">


        <div class="heading-content">

            <span class="eyebrow">
                Grama Niladhari
            </span>


            <h1>
                Volunteer Events
            </h1>


            <p>
                View volunteer events and the citizens who participated.
            </p>

        </div>


        <!-- DASHBOARD -->

        <a
            href="gn_dash.php"
            class="dashboard-link"
        >

            <span>←</span>

            Dashboard

        </a>


    </section>



    <!-- =====================================================
         EVENTS TABLE
         ===================================================== -->

    <?php if (!empty($events)): ?>


        <section class="events-table-container">


            <!-- TABLE HEADER -->

            <div class="table-header">

                <div>

                    <h2>
                        Volunteer Events
                    </h2>


                    <p>

                        <?= count($events) ?>

                        event<?= count($events) !== 1 ? 's' : '' ?>

                        available

                    </p>

                </div>

            </div>



            <!-- TABLE -->

            <div class="table-wrapper">


                <table class="events-table">


                    <thead>

                        <tr>

                            <th>
                                Event
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Volunteers
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php foreach ($events as $event): ?>


                        <tr>


                            <!-- EVENT -->

                            <td>

                                <div class="event-name">


                                    <div class="event-icon">
                                        🌱
                                    </div>


                                    <div>

                                        <strong>
                                            <?= htmlspecialchars($event['Name']) ?>
                                        </strong>


                                        <span>
                                            Event #<?= (int)$event['Event_Id'] ?>
                                        </span>

                                    </div>


                                </div>

                            </td>



                            <!-- DATE -->

                            <td>

                                <div class="table-info">

                                    <span class="info-icon">
                                        📅
                                    </span>

                                    <span>
                                        <?= htmlspecialchars($event['Date']) ?>
                                    </span>

                                </div>

                            </td>



                            <!-- LOCATION -->

                            <td>

                                <div class="table-info">

                                    <span class="info-icon">
                                        📍
                                    </span>

                                    <span>
                                        <?= htmlspecialchars($event['Location']) ?>
                                    </span>

                                </div>

                            </td>



                            <!-- VOLUNTEERS -->

                            <td>

                                <span class="volunteer-badge">

                                    <span>
                                        👥
                                    </span>


                                    <?= (int)$event['Participant_Count'] ?>


                                    <?= ((int)$event['Participant_Count'] === 1)
                                        ? 'Volunteer'
                                        : 'Volunteers'
                                    ?>

                                </span>

                            </td>



                            <!-- ACTION -->

                            <td>

                                <a
                                    href="GN_view_participants.php?id=<?= (int)$event['Event_Id'] ?>"
                                    class="view-btn"
                                >

                                    View Volunteers

                                    <span>
                                        →
                                    </span>

                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        </section>



    <?php else: ?>


        <!-- =================================================
             EMPTY STATE
             ================================================= -->

        <section class="empty-state">


            <div class="empty-icon">
                🌱
            </div>


            <h2>
                No Volunteer Events
            </h2>


            <p>
                Volunteer events created by the Admin will appear here.
            </p>


            <a
                href="gn_dash.php"
                class="dashboard-link"
            >

                <span>←</span>

                Back to Dashboard

            </a>


        </section>


    <?php endif; ?>


</main>



<?php include 'gn_footer.php'; ?>


</body>

</html>


