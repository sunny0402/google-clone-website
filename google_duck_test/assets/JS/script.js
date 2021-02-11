var timer; //accessible to all javascript code

$(document).ready(function () {
  //query sites table
  // while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
  //     $id = $row["id"];
  //     $url = $row["url"];

  //SiteResultsProvider.php
  //<h3 class='title'>
  //<a class='result' href='$url' data-linkId='$id'>
  //   $title
  //</a>

  //work with <a class='result' href='$url' data-linkId='$id'> below

  $(".result").on("click", function () {
    // console.log("I was clicked.");

    //this refers to the object that the event was called on
    //$(".result")
    var url = $(this).attr("href");
    var id = $(this).attr("data-linkId");
    //if id null
    if (!id) {
      alert("data-linkId attribute not found");
    }

    increaseLinkClicks(id, url);

    //do not do a links default behaviour and go to another page
    //so when clicked will not go to result because of return false;
    return false;
  });

  //isInitLayout ... every time load image call masonry layout
  var grid = $(".imageResults");
  //layoutComplete is a masonry event
  grid.on("layoutComplete", function () {
    $(".gridItem img").css("visibility", "visible");
  });

  grid.masonry({
    itemSelector: ".gridItem",
    columnWidth: 200,
    gutter: 6,
    transitionDuration: "0.8s",
    isInitLayout: false,
  });

  //use a selector and then call fancybox and pass some options in
  // https://fancyapps.com/fancybox/3/
  //call the code on every element which has [data-fancybox] attribute

  $("[data-fancybox]").fancybox({
    caption: function (instance, item) {
      var caption = $(this).data("caption") || "";
      var siteUrl = $(this).data("siteurl") || "";

      if (item.type === "image") {
        caption =
          (caption.length ? caption + "<br />" : "") +
          '<a href="' +
          item.src +
          '">View image</a><br>' +
          '<a href="' +
          siteUrl +
          '">Visit page</a>';
      }

      return caption;
    },
    //fancybox after show do this code
    afterShow: function (instance, item) {
      //item.src is the link to take you to the actual image itself
      increaseImageClicks(item.src);
    },
  });
});

function loadImage(src, className) {
  //   console.log(src);
  var image = $("<img>");
  image.on("load", function () {
    //from ImageResultsProvider.php
    //$resultsHtml .= "<div class='gridItem image$count'>
    //<a href='$imageUrl'>
    //so if image "load" then append it
    $("." + className + " a").append(image);

    clearTimeout(timer);

    timer = setTimeout(function () {
      //call masonry code so does the pretty layout write away
      //timer so does not call the masonry() one after the other as images load
      $(".imageResults").masonry();
    }, 500);
  });

  //if error update images table column broken
  image.on("error", function () {
    // console.log("broken");
    $("." + className).remove();
    //first param is url of that path we want to make the request to, second param is some data
    $.post("ajax/setBroken.php", { src: src });
  });

  //src attribute of image a jquery object will have the value src
  //so src will indicate the url of the image like any image tag <img src="some_site.com"
  image.attr("src", src);
}

//Want to updated clicks in database and then take to link
//linkId is row in database
//this fn will increase the value in the value in the column clicks in sites table
function increaseLinkClicks(linkId, url) {
  //send this post request to the php file which will make a call to db
  //variable called linkId and the value is linkId
  $.post("ajax/updateLinkCount.php", { linkId: linkId })
    //when done with ajax request do the following function
    //result variable stores any of the output of updateLinkCount.php
    .done(function (result) {
      if (result != "") {
        //if result is a string it means there was an error
        alert(result);
        return;
      }
      //but result variable is empty if no error
      //so take user to clicked url
      //so by using increaseLinkClicks we are able to display search results in order of popularity
      window.location.href = url;
    });
}

function increaseImageClicks(imageUrl) {
  $.post("ajax/updateImageCount.php", { imageUrl: imageUrl }).done(function (
    result
  ) {
    if (result != "") {
      alert(result);
      return;
    }
  });
}
