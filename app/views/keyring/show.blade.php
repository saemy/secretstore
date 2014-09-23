<div class="entries">
    @foreach ($keyring->getEntries() as $entry)
        <?php $show = sprintf('keyring("%s").showSecret("%s"); return false;', $keyring->getId(), $entry->getId()); ?>
        <?php $hide = sprintf('keyring("%s").hideSecret("%s"); return false;', $keyring->getId(), $entry->getId()); ?>
        <div class="entry" id="entry-{{{ $keyring->getId() }}}-{{{ $entry->getId() }}}">
            {{{ $entry->getDisplayName() }}}
            <div class="secret">
                <span></span>

                <a href="#" onclick="{{{ $show }}}" class="show">show</a>
                <a href="#" onclick="{{{ $hide }}}" class="hide" style="display: none;">hide</a>
            </div>
        </div>
    @endforeach
</div>