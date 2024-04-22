<?php
chdir("../..");
error_reporting(E_ERROR | E_PARSE);
if (isset($_POST["mode"])) {
    switch ($_POST["mode"]) {
        case 'updateRoom':
        case 'addRoom':
            $center = $_POST["site"];
            $room = $_POST["room"];
            $info = $_POST["info"];
            if (!$center) break;
            $filepath = './partners/default/centers/' . $center . '/rooms.cfg';
            $parsed_ini = parse_ini_file($filepath, true);
            if (isset($_POST["oldRoom"]) && $room != $_POST["oldRoom"]) unset($parsed_ini[$_POST["oldRoom"]]);
            $parsed_ini[$room]["info"] = $info;
            $handle = fopen($filepath, 'w');
            $content = '';
            foreach ($parsed_ini as $section => $values) {
                $content .= "[" . $section . "]\n";
                foreach ($values as $key => $value) {
                    $content .= $key . " = \"" . $value . "\"\n";
                }
                $content .= "\n";
            }
            fwrite($handle, $content);
            fclose($handle);
            break;


        case 'addCenter': case 'updateCenter':
            $center = $_POST["site"];
            $name = $_POST["name"];
            $type = $_POST["type"];
            $cluster = $_POST["cluster"];
            $address = $_POST["address"];

            if (!$center) break;
            $filepath = './partners/default/centers.cfg';
            $parsed_ini = parse_ini_file($filepath, true);
            if (!array_key_exists($center, $parsed_ini)) {
                mkdir("./partners/default/centers/" . $center);
            } elseif( $_POST["mode"] == "addCenter") {
                echo '<script language="javascript">';
                echo 'alert("center already exists")';
                echo '</script>';
                break;
            }
            $parsed_ini[$center]["name"] = $name;
            $parsed_ini[$center]["type"] = $type;
            $parsed_ini[$center]["cluster"] = $cluster;
            $parsed_ini[$center]["address"] = $address;
            $handle = fopen($filepath, 'w');

            $content = '';
            foreach ($parsed_ini as $section => $values) {
                $content .= "[" . $section . "]\n";
                foreach ($values as $key => $value) {
                    $content .= $key . " = \"" . $value . "\"\n";
                }
                $content .= "\n";
            }
            fwrite($handle, $content);
            fclose($handle);
            break;
    }
}
$listCenters = parse_ini_file('./partners/default/centers.cfg', true);
$listRooms = [];
foreach ($listCenters as $key => $value) {
    $listRooms[$key] = parse_ini_file('./partners/default/centers/' . $key . '/rooms.cfg', true);
}

?>

<html>

<head>
    <style>
        *,
        ::after,
        ::before {
            box-sizing: border-box;
        }

        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin-bottom: .5rem;
            margin-top: 0;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.2;
            color: inherit;
        }

        .mb-0 {
            margin: 0;
        }

        .pb-2,
        .py-2 {
            padding-bottom: .5rem !important;
        }

        .px-2 {
            padding-left: 0.5 !important;
            padding-right: 0.5 !important;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
        }

        .container-fluid {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important;
        }

        .d-flex {
            display: -webkit-box !important;
            display: -ms-flexbox !important;
            display: flex !important;
        }

        .row {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-2 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 16.666667%;
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col-4 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 33.333333%;
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col-5 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 41.666667%;
            flex: 0 0 41.666667%;
            max-width: 41.666667%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col-6 {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 50%;
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .justify-content-between {
            -webkit-box-pack: justify !important;
            -ms-flex-pack: justify !important;
            justify-content: space-between !important;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .btn-link {
            font-weight: 400;
            color: #050606;
            background-color: transparent;
        }

        .btn-link:hover {
            color: #20ccff;
        }

        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
            text-decoration: none;
        }

        .card {
            position: relative;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: .25rem;
        }

        .card-header {
            padding: .75rem 1.25rem;
            margin-bottom: 0;
            background-color: rgba(0, 0, 0, .03);
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .card-header:first-child {
            border-radius: calc(.25rem - 1px) calc(.25rem - 1px) 0 0;
        }

        .collapse.show {
            display: block;
        }

        .collapse {
            display: none;
        }

        .card-body {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: inline-block;
            margin-bottom: .5rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .form-control:disabled,
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }

        button,
        input,
        optgroup,
        select,
        textarea {
            margin: 0;
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
            overflow: visible;
        }

        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .fade {
            opacity: 0;
            transition: opacity .15s linear;
        }

        .fade.show {
            opacity: 1;
        }

        .tab-content>.tab-pane {
            display: none;
        }

        .tab-content>.active {
            display: block;
        }
    </style>

</head>

<body>
    <div class="container-fluid ">
        <h1 class="d-flex justify-content-center"> Management center and room </h1>
        <div class="row px-2">
            <div class="col-4">
                <div class="d-flex justify-content-between pb-2">
                    <h3>Center</h3>
                    <a class="btn btn-info add-new-center" role="button" style="color: white;" onclick="showNewCenterForm()">
                        +
                    </a>
                </div>
            </div>
            <div class="col-4"></div>
            <div class="col-4" id="room-title" style="display:none">
                <div class="d-flex justify-content-between pb-2">
                    <h3>List Room</h3>
                    <a class="btn btn-info" role="button" aria-controls="addRoomCollapse" id="btn-new-room" onclick="showNewRoomForm()">
                        +
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-4">

                <div class="accordion" id="accordionExample" style="overflow-y: auto;height:600px">
                    <?php
                    foreach ($listCenters as $site => $center) {
                    ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between" id="heading<?php echo $site ?>">
                                <h5 class="mb-0">
                                    <button class="btn btn-link btn-show-Center" onclick="showCenter(this)" data-value="<?php echo $site ?>" data-name="<?php echo $center["name"] ?>" data-type="<?php echo $center["type"] ?>" data-cluster="<?php echo $center["cluster"] ?>" data-address="<?php echo $center["address"] ?>" type="button" data-toggle="collapse" data-target="#collapse<?php echo $site ?>" aria-expanded="false" aria-controls="collapse<?php echo $site ?>">
                                        <strong><?php echo $site ?></strong>
                                    </button>
                                </h5>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-4 infomation-form ">

                <div class="collapse <?php if (isset($_POST["site"])) echo "show"; ?>" id="addCenterCollapse">
                    <div class="card card-body">
                        <form action="#" method="POST" id="add-center-form" onchange="updateCenter(this)">
                            <div class="form-group">
                                <label for="centerSite">Site</label>
                                <input type="text" class="form-control" id="centerSite" placeholder="Enter Site" name="site" value="<?php echo $_POST["site"] ?>">
                            </div>
                            <div class="form-group">
                                <label for="centerName">Name</label>
                                <input type="text" class="form-control" id="centerName" placeholder="Enter Name" name="name">
                            </div>
                            <div class="form-group">
                                <label for="centerType">Type</label>
                                <input type="text" class="form-control" id="centerType" placeholder="Enter Type" name="type" value="internal">
                            </div>
                            <div class="form-group">
                                <label for="centerCluster">Cluster</label>
                                <input type="text" class="form-control" id="centerCluster" placeholder="Enter Cluster" name="cluster">
                            </div>
                            <div class="form-group">
                                <label for="centerAddress">Address</label>
                                <input type="text" class="form-control" id="centerAddress" placeholder="Enter Address" name="address">
                            </div>
                            <input type="hidden" name="mode" value="addCenter" id="centerMode" />
                            <button type="submit" style="display: none;" id="centerSubmit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-4">

                <div class="tab-content" id="nav-tabContent" style="overflow-y: auto; height:600px">
                    <div class="collapse" id="addRoomCollapse">
                        <div class="card card-body">
                            <h4>Add new room</h4>
                            <form action="#" method="POST" id="newRoomForm">
                                <input type="hidden" name="site" id="add-room-site" />
                                <div class="row">
                                    <div class="col-5">
                                        <input type="text" class="form-control" id="roomName" placeholder="Enter room name" name="room">
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control" id="info" placeholder="Enter info" name="info">
                                    </div>
                                    <div class="col-2">
                                        <button type="submit" name="mode" class="btn btn-info" value="addRoom">Save </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                    <?php
                    foreach ($listCenters as $site => $center) {
                    ?>
                        <div class="tab-pane fade <?php if (isset($_POST["room"]) && $_POST["site"] == $site) echo 'show active';
                                                    else echo ''; ?>" id="list-room-<?php echo $site ?>" role="tabpanel" aria-labelledby="list-home-list">
                            <?php
                            if (!$listRooms[$site]) {
                                echo 'There is no data in this center';
                            } else
                                foreach ($listRooms[$site] as $room => $info) {
                            ?>
                                <div class="card">
                                    <div class="card-header n" id="headinglist-room-<?php echo '' . $site . '-' . $room ?>">
                                        <form action="#" method="POST" class="mb-0 d-flex justify-content-between row update-room-form" onchange="updateRoom(this)">
                                            <input type="hidden" name="site" value="<?php echo $site; ?>" />
                                            <input type="hidden" name="oldRoom" value="<?php echo $room; ?>" />
                                            <input type="hidden" name="mode" value="updateRoom" />
                                            <div class="form-group col-6">
                                                <small id="emailHelp" class="form-text text-muted">Room</small>
                                                <input type="text" class="form-control" name="room" value="<?php echo $room; ?>" />
                                            </div>
                                            <div class="form-group col-6">
                                                <small id="emailHelp" class="form-text text-muted">Info</small>
                                                <input type="text" class="room-info form-control" id="info<?php echo '' . $site . '-' . $room  ?>" placeholder="Enter info" name="info" value="<?php echo $info["info"] ?>">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>


    <script>
        let checkPost = <?php if (isset($_POST["site"])) echo "'" . $_POST["site"] . "'";
                        else echo 0; ?>;
        if (checkPost != 0) {
            center = document.querySelector('.btn-show-Center[data-value=' + checkPost + ']');
            document.getElementById("centerSite").value = center.getAttribute("data-value");
            document.getElementById("centerSite").setAttribute("readonly", true);
            document.getElementById("centerName").value = center.getAttribute("data-name");
            document.getElementById("centerCluster").value = center.getAttribute("data-cluster");
            document.getElementById("centerType").value = center.getAttribute("data-type");
            document.getElementById("centerAddress").value = center.getAttribute("data-address");
            document.getElementById("centerSubmit").textContent = "Update";
            document.getElementById("centerSubmit").style.display = "none";
            document.getElementById("centerMode").value = "updateCenter";
            document.getElementById("room-title").style.display = 'unset';
            let removeClass = document.querySelectorAll('.tab-pane');
            removeClass.forEach(e => {
                e.classList.remove('show');
                e.classList.remove('active');
            });
            let addClass = document.getElementById('list-room-' + center.getAttribute("data-value"));
            addClass.classList.add("show");
            addClass.classList.add("active");
            document.getElementById('add-room-site').value = center.getAttribute("data-value");
        }

        function showCenter(a) {
            let site = a.getAttribute("data-value");
            let name = a.getAttribute("data-name");
            let type = a.getAttribute("data-type");
            let cluster = a.getAttribute("data-cluster");
            let address = a.getAttribute("data-address");

            document.getElementById("centerSite").value = site;
            document.getElementById("centerSite").setAttribute('readonly', true);
            document.getElementById("centerName").value = name;
            document.getElementById("centerCluster").value = cluster;
            document.getElementById("centerType").value = type;
            document.getElementById("centerAddress").value = address;
            document.getElementById("centerSubmit").textContent = "Update";
            document.getElementById("centerSubmit").style.display = "none";
            document.getElementById("centerMode").value = "updateCenter";
            document.getElementById("room-title").style.display = 'unset';
            let checkClass = document.getElementById('addCenterCollapse');
            if (checkClass.classList.contains('show')) {} else {
                checkClass.classList.add('show');
            }
            let removeClass = document.querySelectorAll('.tab-pane');
            removeClass.forEach(e => {
                e.classList.remove('show');
                e.classList.remove('active');
            });
            let addClass = document.getElementById('list-room-' + site);
            addClass.classList.add("show");
            addClass.classList.add("active");
            document.getElementById('add-room-site').value = site;
        }

        function showNewCenterForm() {
            document.getElementById("centerSite").value = '';
            document.getElementById("centerSite").removeAttribute('readonly');
            document.getElementById("centerName").value = '';
            document.getElementById("centerCluster").value = '';
            document.getElementById("centerType").value = '';
            document.getElementById("centerAddress").value = '';
            document.getElementById("centerSubmit").textContent = "Save";
            document.getElementById("centerSubmit").style.display = "unset";
            document.getElementById("centerMode").value = "addCenter";
            let checkClass = document.getElementById('addCenterCollapse');
            if (checkClass.classList.contains('show')) {} else {
                checkClass.classList.add('show');
            }
        }

        function showNewRoomForm() {
            let checkClass = document.getElementById('addRoomCollapse');
            if (checkClass.classList.contains('show')) {} else {
                checkClass.classList.add('show');
            }
        }

        function updateRoom(a) {
            a.submit();
        }

        function updateCenter(a) {
            let checkUpdate = document.getElementById("centerSubmit").textContent;
            if (checkUpdate == "Update")
                a.submit();
        }
    </script>
</body>

</html>