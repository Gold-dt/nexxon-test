<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toplay - CS2 Hours Played</title>
	<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" >
	<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
	<meta name="description" content="List the players banned on the server." />
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php
// Include header.php
include 'header.php';

// Include connection.php for connection details
include 'src/simpleadmincon.php';

// Connect to the database corresponding to the selected server
$selectedServer = isset($_GET['server']) ? $_GET['server'] : null;
$conn = connectToDatabase($selectedServer);

// Check if the connection was successfully established
if ($conn) {
    // Retrieve the list of servers after the connection is established
    $serverList = getServerList($conn);

    // Set the default server if it is not already set
    if (!isset($_GET['server'])) {
        header("Location: ?server=" . $conn->defaultServer);
        exit();
    }

    // Redirect to the default server if the 'server' parameter is not valid
    if (!in_array($_GET['server'], $serverList)) {
        header("Location: ?server=" . $conn->defaultServer);
        exit();
    }
} else {
    // Handle the error or return an appropriate value
    die("Database connection failed.");
}


?>
		
	
<script>
$(document).ready(function(){
    jQuery(".cell1").click(function() {
        var btn_call_action = jQuery(this);

        var data_playersteamid = jQuery(this).attr("data-playersteamid");
        var data_playername = jQuery(this).attr("data-playername") || 'Unknown';
        var data_adminsteamid = jQuery(this).attr("data-adminsteamid");
        var data_reason = jQuery(this).attr("data-reason") || 'Unknown';
        var data_duration = jQuery(this).attr("data-duration") || 'Unknown';
        var data_ends = jQuery(this).attr("data-ends") || 'Unknown';
        var data_created = jQuery(this).attr("data-created") || 'Unknown';
        var data_status = jQuery(this).attr("data-status") || 'Unknown';
        var data_duration = jQuery(this).attr("data-duration") || 'Unknown';
        var data_adminname = jQuery(this).attr("data-adminname") || 'Unknown';

        var steamProfileLink = '';
        if (data_playersteamid) {
            steamProfileLink = '<b>Steam Profile:</b> <a href="https://steamcommunity.com/profiles/' + data_playersteamid + '" target="_blank" rel="noopener">' + data_playername + '</a><br>';
        } else {
            steamProfileLink = '<b>Steam Profile:</b> ' + data_playername + '<br>';
        }

        Swal.fire({
            icon: "info",
            html: steamProfileLink + '<b>Admin:</b> ' + (data_adminname !== 'Unknown' ? data_adminname : 'Unknown') + '<br><b>Reason:</b> ' + (data_reason !== 'Unknown' ? data_reason : 'Unknown'),
            confirmButtonText: 'Close'
        })
    });
});
</script>


<body>

<div class="server-buttons">
    <?php
    $serverList = getServerList($conn);
    foreach ($serverList as $server) {
        echo '<button class="server-button" onclick="setPrefixAndChangeServer(\'' . $server . '\', \'\')">' . strtoupper($server) . '</button>';
    }
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Set the active class based on the current server parameter
        var searchParams = new URLSearchParams(window.location.search);
        var currentServer = searchParams.get('server');
        setActiveButton(currentServer);
    });

function changeServer(selectedServer) {
    // Set the 'server' parameter to the new server and remove other parameters
    window.location.href = '?' + new URLSearchParams({ 'server': selectedServer }).toString();
}

function setPrefixAndChangeServer(selectedServer, prefix) {
    // Save the prefix in a cookie
    setCookie("prefix", prefix, 30);

    // Redirect to the selected server
    changeServer(selectedServer);
}

function setActiveButton(serverName) {
    var buttons = document.querySelectorAll('.server-button');
    buttons.forEach(function (button) {
        button.classList.remove('active');
        if (button.innerText.toLowerCase() === serverName.toLowerCase()) {
            button.classList.add('active');
        }
    });
}

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }	
	
</script>


    <h1>List the players banned on the server.</h1>
	
<!-- Search form for names -->
<div class="searchdiv">
    <form method="GET" action="">
        <input type="text" id="search" name="search" placeholder="Enter name...">
        <input type="hidden" name="server" value="<?php echo $selectedServer; ?>">
        <button type="submit">Search</button>
    </form>
    <?php
    // Check if a search has been performed
    if (isset($_GET['search'])) {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?server=' . $selectedServer . '" class="back-button">Back to All</a>';
    }
    ?>
</div><br>

<?php


// Set the number of records per page.
$recordsPerPage = 15;

// Set the number of visible pages in pagination.
$visiblePages = 3;

// Current page (default is 1 if not set)
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculate the offset to retrieve the correct records for the current page
$offset = ($current_page - 1) * $recordsPerPage;

// The prefix is set based on the selected server.
$prefix = $conn->servers[$selectedServer]['prefix'];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query with the prefix.
if (!empty($search)) {
   // If there is a search value, exclude null values for "player_name".
    $query = "SELECT player_steamid, player_name, admin_steamid, admin_name, reason, duration, ends, created, status FROM {$prefix}sa_bans WHERE player_name LIKE :search ORDER BY created DESC LIMIT :offset, :limit";
} else {
    // If there is no search value, include null values for "player_name".
    $query = "SELECT player_steamid, player_name, admin_steamid, admin_name, reason, duration, ends, created, status FROM {$prefix}sa_bans ORDER BY created DESC LIMIT :offset, :limit";
}

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if (!empty($search)) {
    // If there is a search value, set the parameter for search.
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the query was successful
if ($result) {
    // Display the beginning part of the wrapper
    echo '<div class="wrapper">';

    // Display the beginning part of the table
    echo '<div class="table">
            <div class="row header">
                <div class="cell">#</div>
                <div class="cell">Name</div>
                <div class="cell">Duration</div>
                <div class="cell">Created</div>
                <div class="cell">Ends</div>
                <div class="cell">Status</div>
            </div>';

// Calculate the total number of records (not just those on the current page)
        $countQuery = "SELECT COUNT(*) as total FROM {$prefix}sa_bans WHERE player_name LIKE :search";
        $countStmt = $conn->prepare($countQuery);
		$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $countStmt->execute();
        $totalCount = $countStmt->fetchColumn();

        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $recordsPerPage);

        // Calculate the range of visible pages based on the current page
        $startPage = max(1, $current_page - floor($visiblePages / 2));
        $endPage = min($totalPages, $startPage + $visiblePages - 1);

        // Calculate the order number of the first row on the page
        $startRowNumber = ($current_page - 1) * $recordsPerPage + 1;

		
foreach ($result as $row) {
    $status = $row["status"];
    $statusColor = ''; // 
    $boldStyle = ''; // 

    // Set the color and bold style based on the value from "status".
    switch ($status) {
        case 'UNBANNED':
            $statusColor = 'green';
            $boldStyle = 'font-weight: bold;';
            break;
        case 'ACTIVE':
            $statusColor = 'red';
            $boldStyle = 'font-weight: bold;';
            break;
        case 'EXPIRED':
            $statusColor = 'green';
            $boldStyle = 'font-weight: bold;';
            break;
        default:
            // If "status" is empty or null, set the color, text to "Unknown," and bold style.
            $statusColor = 'gray'; // 
            $status = 'Unknown';
            $boldStyle = 'font-weight: bold;';
            break;
    }
	
	  // Set default values for empty or null fields
    $playerName = $row["player_name"] ?: 'Unknown';
    $adminName = $row["admin_name"] ?: 'Unknown';
    $reason = $row["reason"] ?: 'Unknown';
	$duration = ($row["duration"] !== null && $row["duration"] !== '') ? $row["duration"] : 'Unknown';
	$durationDisplay = ($duration !== 'Unknown' && $duration != 0) ? $duration . ' Minutes' : 'Permanent';
    $ends = $row["ends"] ?: 'Unknown';
    $created = $row["created"] ?: 'Unknown';

  echo '<div class="row">
            <div class="cell" data-title="#">' . $startRowNumber . '</div>
            <div class="cell cell1" data-title="Name" data-playersteamid="' . $row["player_steamid"] . '" data-adminname="' . $row["admin_name"] . '" data-playername="' . $row["player_name"] . '" data-adminsteamid="' . $row["admin_steamid"] . '" data-reason="' . $row["reason"] . '" data-duration="' . $row["duration"] . '" data-ends="' . $row["ends"] . '" data-created="' . $row["created"] . '" data-status="' . $row["status"] . '">' . $playerName . '</div>
			<div class="cell" data-title="Duration">' . $durationDisplay . '</div>
            <div class="cell" data-title="Created">' . $created . '</div>
            <div class="cell" data-title="Ends">' . $ends . '</div>
            <div class="cell" data-title="status" style="color: ' . $statusColor . ';' . $boldStyle . '">' . $status . '</div>
          </div>';
    $startRowNumber++;
}




    // Display the ending part of the table
        echo '</div>';

// Display pagination buttons with improved CSS
echo '<div class="pagination">';
// Button for the first page
if ($startPage > 1) {
    echo '<a href="?page=1&server=' . $selectedServer;
    if (!empty($search)) {
        echo '&search=' . $search;
    }
    echo '">1</a>';
    if ($startPage > 2) {
        echo '<span>...</span>';
    }
}

// Display the buttons for each page in the calculated range
for ($i = $startPage; $i <= $endPage; $i++) {
    $activeClass = ($current_page == $i) ? 'active' : '';
    echo '<a href="?page=' . $i . '&server=' . $selectedServer;
    if (!empty($search)) {
        echo '&search=' . $search;
    }
    echo '" class="' . $activeClass . '">' . $i . '</a>';
}

// Button for the last page
if ($endPage < $totalPages) {
    if ($endPage < $totalPages - 1) {
        echo '<span>...</span>';
    }
    echo '<a href="?page=' . $totalPages . '&server=' . $selectedServer;
    if (!empty($search)) {
        echo '&search=' . $search;
    }
    echo '">' . $totalPages . '</a>';
}
echo '</div>';

        // Close the wrapper
        echo '</div>';
    } else {
        echo '<div class="errordiv">Nothing was found!' . $stmt->errorInfo()[2] . '</div>';
    }

// Close the connection to the database
$conn = null;
?>
</body>
</html>
