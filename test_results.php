<?php
include('inc/init.php');
include('inc/header_homepage.php');
include('inc/menu.php');

$date = isset($_GET['date']) ? $_GET['date'] : '';

if( strpos(URL,"dev")==false && strpos(URL,"localhost")==false){ // LIVE
    if ( strpos(URL,"nz") == false ) {
        $vjdTestLink = "view_job_details.php?id=517618";
        $vpdTestLink = "view_property_details.php?id=111018";
    }
    else {
        $vjdTestLink = "view_job_details.php?id=420414";
        $vpdTestLink = "view_property_details.php?id=165828";
    }
}
else {
    $vjdTestLink = "view_job_details.php?id=827";
    $vpdTestLink = "view_property_details.php?id=119";
}
?>

        <div id="mainContent" class="homepage">
            <div style="text-align: left;">
                <form action="test_results.php" method="get">
                    <input type="date" name="date" placeholder="2020-01-01" style="padding: 0.75em;" value="<?= $_GET['date'] ?>" />
                    <button type="submit" style="padding: 0.75em; border-radius: 6px;">Filter</button>
                </form>
                <br class="clearfloat" />
                <a href="<?= $vjdTestLink ?>" target="_blank"><button>Sample VJD</button></a>
                <a href="<?= $vpdTestLink ?>" target="_blank"><button>Sample VPD</button></a>
                <br class="clearfloat" />
                <br class="clearfloat" />
            </div>
            <table cellspacing="10">
                <thead>
                    <tr>
                        <th>
                            Page
                        </th>
                        <th>
                            Total Requests
                        </th>
                        <th>
                            Avg (seconds)
                        </th>
                        <th>
                            Min (seconds)
                        </th>
                        <th>
                            Max (seconds)
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $results = mysql_query("
                        SELECT
                            page,
                            COUNT(duration) AS count,
                            AVG(duration) AS average,
                            MIN(duration) AS minimum,
                            MAX(duration) AS maximum
                        FROM
                            logged_page_durations AS lpd
                        WHERE
                            created LIKE '{$date}%'
                        GROUP BY (page)
                        ORDER BY page
                    ");
                    while (($row = mysql_fetch_array($results))):

                    switch($row['page']) {
                        case 'VJD':
                            $url = $vjdTestLink;
                        break;
                        case 'VPD':
                            $url = $vpdTestLink;
                        break;
                        default:
                            $url = null;
                        break;
                    }
                    if ($url) {
                        $link = '<a href="'.$url.'" target="_blank">sample link</a>';
                    }
                    else {
                        $link = '';
                    }
                    ?>
                    <tr>
                        <td><?= $row['page'] ?></td>
                        <td style="text-align: right;"><?= $row['count'] ?></td>
                        <td style="text-align: right;"><?= ($row['average']*0.001) ?></td>
                        <td style="text-align: right;"><?= ($row['minimum']*0.001) ?></td>
                        <td style="text-align: right;"><?= ($row['maximum']*0.001) ?></td>
                        <td><?= $link ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </body>
</html>