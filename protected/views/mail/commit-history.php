<h3>Release notes for <?php echo CHtml::link($environment->server_url, $environment->server_url); ?></h3>

<p><b>Features</b></p>
<?php if (isset($commits[Commits::TYPE_FEATURE])): ?>
    <?php foreach ($commits[Commits::TYPE_FEATURE] as $commit): ?>
        <?php echo strtr('{task_id}: {description} {link} ({date})', array(
            '{task_id}'     => $commit->task_id,
            '{description}' => $commit->description,
            '{link}'        => CHtml::link($commit->url, $commit->url),
            '{date}'        => $commit->date->format('m/d/Y'),
        )); ?><br />
    <?php endforeach; ?>
<?php else: ?>
    - No recent commits -<br />
<?php endif; ?>

<p><b>Defects</b></p>
<?php if (isset($commits[Commits::TYPE_DEFECT])): ?>
    <?php foreach ($commits[Commits::TYPE_DEFECT] as $commit): ?>
        <?php echo strtr('{task_id}: {description} {link} ({date})', array(
                '{task_id}'     => $commit->task_id,
                '{description}' => $commit->description,
                '{link}'        => CHtml::link($commit->url, $commit->url),
                '{date}'        => $commit->date->format('m/d/Y'),
        )); ?><br />
    <?php endforeach; ?>
<?php else: ?>
    - No recent commits -<br />
<?php endif; ?>

<p><b>Changes</b></p>
<?php if (isset($commits[Commits::TYPE_CHANGE])): ?>
    <?php foreach ($commits[Commits::TYPE_CHANGE] as $commit): ?>
        <?php echo strtr('{task_id}: {description} {link} ({date})', array(
                '{task_id}'     => $commit->task_id,
                '{description}' => $commit->description,
                '{link}'        => CHtml::link($commit->url, $commit->url),
                '{date}'        => $commit->date->format('m/d/Y'),
        )); ?><br />
    <?php endforeach; ?>
<?php else: ?>
    - No recent commits -<br />
<?php endif; ?>
