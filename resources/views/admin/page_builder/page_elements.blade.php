{{-- Here must have the draggable class in the each elements --}}

<h5 class="mb-3 pb-2 border-bottom">Buttons:</h5>
<div class="d-flex gap-2 align-items-center flex-wrap justify-content-between">
    <a href="#" class="draggable cursor-move py-2">Link</a>
    <a href="#" class="draggable cursor-move eBtn gradient">Gradient</a>
    <a href="#" class="draggable cursor-move btn-black-arrow1">Black</a>
    <a href="#" class="draggable cursor-move theme-btn1">Success</a>
    <a href="#" class="draggable cursor-move border-btn1">Success Outline</a>
    <a href="#" class="draggable cursor-move btn btn-danger-1">Danger curved</a>
</div>

<script>
    initialize_draggable('clone');
</script>
