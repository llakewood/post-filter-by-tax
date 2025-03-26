jQuery(document).ready(function ($) {
    $('#cactusFilter select').on('change', function () {
        var selectedCategory = $('#categoryFilter').val();
        var selectedTag = $('#tagFilter').val();
        

        $('.cactus-filter-card').each(function () {
            var card = $(this);
            var hasCategory = selectedCategory === '' || card.hasClass(selectedCategory);
            var hasTag = selectedTag === '' || card.hasClass(selectedTag);
            // var cardCount = $('.cactus-filter-card').length;

            if (hasCategory && hasTag) {
                card.removeClass('hide');
            } else {
                card.addClass('hide');
            }
        });


        //are there visible cards in this filter?
        var cardCount = $('.cactus-filter-card').length;
        var hiddenCardCount = $('.cactus-filter-card.hide').length;
        var activeCardCount = cardCount - hiddenCardCount;
        if(activeCardCount == 0) { 
            $('.no-results').removeClass('hide') } 
        else { 
            $('.no-results').addClass('hide');
        }

    });

    // Reset Filters via click
    $('.reset-filters').on('click', function () {
        $('.cactus-filter-card').each(function () {
            $(this).removeClass('hide');
        });

        $('#categoryFilter').val($("#categoryFilter option:first").val());
        $('#tagFilter').val($("#categoryFilter option:first").val());
        $('.no-results').addClass('hide');
    });

});