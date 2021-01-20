<?php
require __DIR__ . '/_header.php';

$packages = isset($project['composer']['packages']) ? $project['composer']['packages'] : [];
usort($packages, function ($a, $b) {
    return strcmp($a->name, $b->name);
});
if ([] === $packages) {
    echo '<div class="row"><div class="column"><div class="bloc">No composer.json file found in this project</div></div></div>';
    require __DIR__ . '/_footer.php';
    return;
}
?>

<div class="row">
    <div class="column">
        <div class="bloc">
            <h4><?php echo count($packages); ?> Composer dependencies</h4>

            <div class="list">
                <?php foreach ($packages as $package) { ?>
                    <div class="list-item">
                        <div class="list-item-title">
                            <a target="_blank" href="https://packagist.org/packages/<?php echo $package->name; ?>">
                                <?php echo $package->name; ?>
                            </a>
                        </div>
                        <div class="help">
                            <div class="help-inner">
                                <?php if ('outdated' === $package->status) { ?>
                                    <span class="help-warning" style="position: absolute; right: 10px;">This package should be updated.</span>
                                <?php } ?>
                                <span class="badge"><?php echo $package->type; ?></span>
                                <?php echo $package->description; ?>
                            </div>
                        </div>

                        <div class="list-item-content">
                            <?php $width = sprintf('%d%%', round(100 / 9, 0)); ?>
                            <table class="table-metrics">
                                <tr>
                                    <td width="<?php echo $width; ?>">
                                        <div class="card-number"><?php echo $package->required; ?></div>
                                        <div class="card-label">Required version</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div class="card-number"><?php echo $package->installed; ?></div>
                                        <div class="card-label">Installed</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div class="card-number"><?php echo $package->latest; ?></div>
                                        <div class="card-label">Latest</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div
                                            class="card-number"><?php echo number_format($package->github_stars, 0); ?></div>
                                        <div class="card-label">Github stars</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div
                                            class="card-number"><?php echo number_format($package->github_forks, 0); ?></div>
                                        <div class="card-label">Github forks</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div
                                            class="card-number"><?php echo number_format($package->github_open_issues, 0); ?></div>
                                        <div class="card-label">Github open issues</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div
                                            class="card-number"><?php echo number_format($package->download_total, 0); ?></div>
                                        <div class="card-label">Total downloads</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div
                                            class="card-number"><?php echo number_format($package->download_monthly, 0); ?></div>
                                        <div class="card-label">Monthly downloads</div>
                                    </td>
                                    <td width="<?php echo $width; ?>">
                                        <div class="card-number">
                                            <?php foreach ($package->license as $license) { ?>
                                                <a target="_blank"
                                                   href="https://spdx.org/licenses/<?php echo $license; ?>.html"><?php echo $license; ?></a>
                                            <?php } ?>
                                        </div>
                                        <div class="card-label">License(s)</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
