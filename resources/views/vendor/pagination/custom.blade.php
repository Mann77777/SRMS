<?php
// Save this file to resources/views/vendor/pagination/custom.blade.php

if ($paginator->hasPages()) : ?>
<nav role="navigation" aria-label="Pagination Navigation">
    <ul class="pagination">
        {{-- Previous Page Link --}}
        <?php if ($paginator->onFirstPage()) : ?>
            <li class="disabled" aria-disabled="true">
                <span>&lt;</span>
            </li>
        <?php else : ?>
            <li>
                <a href="<?= $paginator->previousPageUrl() ?>" rel="prev">&lt;</a>
            </li>
        <?php endif; ?>

        {{-- Pagination Elements --}}
        <?php foreach ($elements as $element) : ?>
            {{-- "Three Dots" Separator --}}
            <?php if (is_string($element)) : ?>
                <li class="disabled" aria-disabled="true">
                    <span><?= $element ?></span>
                </li>
            <?php endif; ?>

            {{-- Array Of Links --}}
            <?php if (is_array($element)) : ?>
                <?php foreach ($element as $page => $url) : ?>
                    <?php if ($page == $paginator->currentPage()) : ?>
                        <li class="active" aria-current="page">
                            <span><?= $page ?></span>
                        </li>
                    <?php else : ?>
                        <li>
                            <a href="<?= $url ?>"><?= $page ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>

        {{-- Next Page Link --}}
        <?php if ($paginator->hasMorePages()) : ?>
            <li>
                <a href="<?= $paginator->nextPageUrl() ?>" rel="next">&gt;</a>
            </li>
        <?php else : ?>
            <li class="disabled" aria-disabled="true">
                <span>&gt;</span>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>