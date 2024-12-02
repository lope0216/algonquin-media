document.addEventListener('DOMContentLoaded', function () {
  const carousel = document.getElementById('pictureCarousel');
  const prevButton = carousel.querySelector('.carousel-control-prev');
  const nextButton = carousel.querySelector('.carousel-control-next');
  const albumId = new URLSearchParams(window.location.search).get('album');

  function getPictureId(next) {
    const activeItem = carousel.querySelector('.carousel-item.active');
    const items = Array.from(activeItem.parentNode.children);
    const activePictureIndex = items.indexOf(activeItem);
    

    let newIndex;
    if (next) {
        // Get next index or loop back to the first if at the end
        newIndex = (activePictureIndex + 1) % items.length;
    } else {
        // Get previous index or loop back to the last if at the start
        newIndex = (activePictureIndex - 1 + items.length) % items.length;
    }

    return items[newIndex].dataset.pictureId;
  }


  function reloadPageWithPictureId(pictureId) {
      const url = new URL(window.location.href);
      url.searchParams.set('picture', pictureId);
      url.searchParams.set('album', albumId);
      window.location.href = url.toString();
  }

  prevButton.addEventListener('click', () => {
      setTimeout(() => {
          const pictureId = getPictureId(false);
          reloadPageWithPictureId(pictureId);
      }, 500);
  });

  nextButton.addEventListener('click', () => {
      setTimeout(() => {
          const pictureId = getPictureId(true);
          reloadPageWithPictureId(pictureId);
      }, 500);
  });
});