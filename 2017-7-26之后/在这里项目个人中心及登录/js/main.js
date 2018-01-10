(function() {
      var newRem = function() {
          var html = document.documentElement;
          html.style.fontSize = 
          // (html.getBoundingClientRect().width > 425) ? (425 / 10 + 'px') :
           html.getBoundingClientRect().width / 10 +'px'
      };
      window.addEventListener('resize', newRem, false);
      newRem();
    })();