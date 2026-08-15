<?php
session_start();
include "../includes/db.php";

// Only Grama Niladhari
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 5) {
    header("Location: ../login.php");
    exit();
}

$gn_id = $_SESSION['user_id'];

// Fetch GN info
$stmt = $pdo->prepare("
    SELECT F_name, L_name, Email, Tele
    FROM users
    WHERE U_Id = ?
");

$stmt->execute([$gn_id]);

$gn = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gn) {
    header("Location: ../login.php");
    exit();
}

$gn_name = $gn['F_name'] . " " . $gn['L_name'];


// Fetch ONLY complaints assigned to this GN
$stmt = $pdo->prepare("
    SELECT c.*, u.F_name, u.L_name, u.Email
    FROM complaint c
    JOIN users u ON c.U_Id = u.U_Id
    WHERE c.Assigned_To = ?
    ORDER BY c.Location ASC, c.C_Id ASC
");

$stmt->execute([$gn_id]);

$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <title>EcoGuard | Grama Niladhari Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/gn_dash3.css"
    >

    <link
        rel="stylesheet"
        href="../css/ecoguard_responsive.css"
    >

    <style>
        .volunteer-events-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 46px;
            box-sizing: border-box;
            padding: 12px 15px;
            margin: 0 0 12px;
            background: hsl(126, 52%, 19%);
            color: #fff;
            text-decoration: none;
            text-align: center;
            border-radius: 8px;
            font-weight: 700;
            line-height: 1.3;
            transition: transform .2s ease, background .2s ease;
            overflow-wrap: anywhere;
        }

        .volunteer-events-btn:hover {
            background: hsl(126, 52%, 25%);
            transform: translateY(-1px);
        }

        .logout-btn {
            width: 100%;
            min-height: 46px;
            box-sizing: border-box;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
        }

        /* Keep the sidebar actions usable on tablets and phones. */
        @media (max-width: 900px) {
            .dashboard {
                display: flex !important;
                flex-direction: column !important;
            }

            .sidebar {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
            }

            .main-content {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
                overflow-x: auto;
            }

            .volunteer-events-btn,
            .logout-btn {
                width: 100%;
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                padding: 15px !important;
            }

            .volunteer-events-btn,
            .logout-btn {
                min-height: 48px;
                padding: 12px 10px;
                font-size: 14px;
            }
        }
    </style>

</head>


<body>

    <?php include 'gn_header.php'; ?>





    <header>

        <h1>
            EcoGuard Grama Niladhari Panel
        </h1>

    </header>


    <div class="dashboard">


        <!-- SIDEBAR -->

        <div class="sidebar">

            <h3>
                <?= htmlspecialchars($gn_name) ?>
            </h3>

            <p>
                <?= htmlspecialchars($gn['Email']) ?>
            </p>

            <p>
                <?= htmlspecialchars($gn['Tele']) ?>
            </p>


            <hr>


            <!-- VOLUNTEER EVENTS -->

            <a
                href="GN_view_volunteer.php"
                class="volunteer-events-btn"
            >
                🌱 Volunteer Events
            </a>

            <!-- CERTIFICATE MANAGEMENT -->

            <a
                href="GN_view_participants.php"
                class="volunteer-events-btn"
            >
                🏆 Manage Certificates
            </a>


            <!-- LOGOUT -->

            <button
                class="logout-btn"
                onclick="location.href='../logout.php'"
            >
                Logout
            </button>

        </div>


        <!-- MAIN CONTENT -->

        <div class="main-content">


            <h2>
                Complaints Assigned To You
            </h2>


            <?php if (!empty($complaints)): ?>


                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Citizen</th>

                            <th>Email</th>

                            <th>Location</th>

                            <th>Severity</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($complaints as $c): ?>


                            <tr
                                onclick="window.location.href='gn_complaint.php?id=<?= $c['C_Id'] ?>'"
                            >

                                <td>
                                    <?= htmlspecialchars(
                                        $c['C_Id']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $c['F_name'] . " " . $c['L_name']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $c['Email']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $c['Location']
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $c['Severity']
                                    ) ?>
                                </td>


                                <td>

                                    <span
                                        class="status <?= strtolower(
                                            str_replace(
                                                ' ',
                                                '-',
                                                $c['Status']
                                            )
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $c['Status']
                                        ) ?>

                                    </span>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <p>
                    No complaints assigned to you.
                </p>


            <?php endif; ?>


        </div>

    </div>


    <?php include 'gn_footer.php'; ?>


</body>

</html