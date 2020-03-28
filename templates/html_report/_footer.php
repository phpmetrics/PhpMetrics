</div>

<!-- Sidebar -->
<?php if(!isset($fullwidth) || $fullwidth === false) {?>
<div id="sidebar">
    <div class="content">
        <div class="logo">
            <a href="http://www.phpmetrics.org"><img src="images/phpmetrics-maintenability.png"
                                                 alt="Logo PhpMetrics"/></a>
            <h1>PhpMetrics</h1>
        </div>
        <div class="links">
            <ul>
                <li>
                    <a href="index.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path
                                d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        Overview
                    </a>
                </li>
                <li>
                    <a href="violations.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path d="M20 8h-2.81c-.45-.78-1.07-1.45-1.82-1.96L17 4.41 15.59 3l-2.17 2.17C12.96 5.06 12.49 5 12 5c-.49 0-.96.06-1.41.17L8.41 3 7 4.41l1.62 1.63C7.88 6.55 7.26 7.22 6.81 8H4v2h2.09c-.05.33-.09.66-.09 1v1H4v2h2v1c0 .34.04.67.09 1H4v2h2.81c1.04 1.79 2.97 3 5.19 3s4.15-1.21 5.19-3H20v-2h-2.09c.05-.33.09-.66.09-1v-1h2v-2h-2v-1c0-.34-.04-.67-.09-1H20V8zm-6 8h-4v-2h4v2zm0-4h-4v-2h4v2z"/>
                        </svg>
                        Violations (<?php echo $sum->violations->total;?>)
                    </a>
                </li>
                <?php if($config->has('junit')) { ?>
                    <li>
                        <a href="junit.html">
                            <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 5h8v2h-8zm0 5.5h8v2h-8zm0 5.5h8v2h-8zM2 11.5C2 15.08 4.92 18 8.5 18H9v2l3-3-3-3v2h-.5C6.02 16 4 13.98 4 11.5S6.02 7 8.5 7H12V5H8.5C4.92 5 2 7.92 2 11.5z"/>
                                <path d="M0 0h24v24H0z" fill="none"/>
                            </svg>
                            Unit testing
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="loc.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs>
                                <path d="M0 0h24v24H0z" id="a"/>
                            </defs>
                            <clipPath id="b">
                                <use overflow="visible" xlink:href="#a"/>
                            </clipPath>
                            <path clip-path="url(#b)"
                                  d="M11 5.08V2c-5 .5-9 4.81-9 10s4 9.5 9 10v-3.08c-3-.48-6-3.4-6-6.92s3-6.44 6-6.92zM18.97 11H22c-.47-5-4-8.53-9-9v3.08C16 5.51 18.54 8 18.97 11zM13 18.92V22c5-.47 8.53-4 9-9h-3.03c-.43 3-2.97 5.49-5.97 5.92z"/>
                        </svg>
                        Size &amp; volume
                    </a>
                </li>
                <li>
                    <a href="complexity.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0V0z" fill="none"/>
                            <path
                                d="M19.07 4.93l-1.41 1.41C19.1 7.79 20 9.79 20 12c0 4.42-3.58 8-8 8s-8-3.58-8-8c0-4.08 3.05-7.44 7-7.93v2.02C8.16 6.57 6 9.03 6 12c0 3.31 2.69 6 6 6s6-2.69 6-6c0-1.66-.67-3.16-1.76-4.24l-1.41 1.41C15.55 9.9 16 10.9 16 12c0 2.21-1.79 4-4 4s-4-1.79-4-4c0-1.86 1.28-3.41 3-3.86v2.14c-.6.35-1 .98-1 1.72 0 1.1.9 2 2 2s2-.9 2-2c0-.74-.4-1.38-1-1.72V2h-1C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10c0-2.76-1.12-5.26-2.93-7.07z"/>
                        </svg>
                        Complexity &amp; defects
                    </a>
                </li>
                <li>
                    <a href="oop.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path
                                d="M17 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zM5 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                        </svg>
                        Object oriented metrics
                    </a>
                </li>
                <li>
                    <a href="relations.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path
                                d="M14 4l2.29 2.29-2.88 2.88 1.42 1.42 2.88-2.88L20 10V4zm-4 0H4v6l2.29-2.29 4.71 4.7V20h2v-8.41l-5.29-5.3z"/>
                        </svg>
                        Object relations
                    </a>
                </li>
                <li>
                    <a href="coupling.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs>
                                <path d="M0 0h24v24H0V0z" id="a"/>
                            </defs>
                            <clipPath id="b">
                                <use overflow="visible" xlink:href="#a"/>
                            </clipPath>
                            <path clip-path="url(#b)"
                                  d="M9.01 14H2v2h7.01v3L13 15l-3.99-4v3zm5.98-1v-3H22V8h-7.01V5L11 9l3.99 4z"/>
                        </svg>
                        Coupling
                    </a>
                </li>
                <li>
                    <a href="packages.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <path d="M11 9.16V2c-5 .5-9 4.79-9 10s4 9.5 9 10v-7.16c-1-.41-2-1.52-2-2.84s1-2.43 2-2.84zM14.86 11H22c-.48-4.75-4-8.53-9-9v7.16c1 .3 1.52.98 1.86 1.84zM13 14.84V22c5-.47 8.52-4.25 9-9h-7.14c-.34.86-.86 1.54-1.86 1.84z"/>
                        </svg>
                        Package oriented metrics
                    </a>
                </li>
                <li>
                    <a href="package_relations.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 20.41L18.41 19 15 15.59 13.59 17 17 20.41zM7.5 8H11v5.59L5.59 19 7 20.41l6-6V8h3.5L12 3.5 7.5 8z"/>
                        </svg>
                        Package relations
                    </a>
                </li>
                <?php if($config->has('git')) { ?>
                <li>
                    <a href="git.html">
                        <img src="images/logo-git.png" alt="">
                        Git
                    </a>
                </li>
                <?php } ?>
                <!--<li>
                    <a href="all.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 3v18h18V3H3zm8 16H5v-6h6v6zm0-8H5V5h6v6zm8 8h-6v-6h6v6zm0-8h-6V5h6v6z"/>
                            <path d="M0 0h24v24H0z" fill="none"/>
                        </svg>
                        All metrics
                    </a>
                </li>-->
                <!--                <li>-->
                <!--                    <a href="#">-->
                <!--                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24"-->
                <!--                             xmlns="http://www.w3.org/2000/svg">-->
                <!--                            <path-->
                <!--                                d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>-->
                <!--                            <path d="M0 0h24v24H0z" fill="none"/>-->
                <!--                        </svg>-->
                <!--                        Third party-->
                <!--                    </a>-->
                <!--                </li>-->
                <!--<li class="sep">
                    <a href="panel.html">
                        <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z"/>
                        </svg>
                        TV Panel
                    </a>
                </li>-->
            </ul>
        </div>
    </div>
</div>
<?php } ?>
</div>

<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/d3.v3.js"></script>
<script type="text/javascript" src="js/d3.hexbin.v0.js"></script>
<script type="text/javascript" src="js/sort-table.min.js"></script>
<script type="text/javascript" src="js/graph-maintainability.js"></script>
<script type="text/javascript" src="js/graph-carousel.js"></script>
<script type="text/javascript" src="js/graph-licenses.js"></script>
<script type="text/javascript" src="js/FileSaver.min.js"></script>

<script src="js/clusterize.min.js"></script>
<link rel="stylesheet" href="css/clusterize.css">

<script type="text/javascript" src="js/classes.js"></script>

<script type="text/javascript">
    var accessibilityEnabled = false;
</script>

</body>
</html>
