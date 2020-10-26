(function ($, Drupal) {
  Drupal.behaviors.indiciaProfile = {
    attach: function (context, settings) {

      particlesJS('layout-wrapper', {
        particles: {
          number: {
            value: 50,
            density: {
              enable: true,
              value_area: 800
            }
          },
          color: {
            value: '#ffffff'
          },
          shape: {
            type: 'circle',
            stroke: {
              width: 0,
              color: '#000000'
            },
            polygon: {
              nb_sides: 5
            },
            image: {
              src: 'img/github.svg',
              width: 100,
              height: 100
            }
          },
          opacity: {
            value: 0.5,
            random: true
          },
          size: {
            value: 4,
            random: true,
            anim: {
              enable: false,
              speed: 40,
              size_min: 0.1,
              sync: false
            }
          },
          line_linked: {
            enable: true,
            distance: 150,
            color: '#ffffff',
            opacity: 0.1,
            width: 1
          },
          move: {
            enable: true,
            speed: 4,
            direction: 'none',
            random: true,
            straight: false,
            out_mode: 'bounce',
            bounce: false,
            attract: {
              enable: false,
              rotateX: 600,
              rotateY: 1200
            }
          }
        },
        interactivity: {
          detect_on: 'canvas',
          events: {
            onhover: {
              enable: false,
              mode: 'bubble'
            },
            onclick: {
              enable: true,
              mode: 'repulse'
            },
            resize: true
          },
          modes: {
            grab: {
              distance: 400,
              line_linked: {
                opacity: 1
              }
            },
            bubble: {
              distance: 200,
              size: 15,
              duration: 2,
              opacity: 5,
              speed: 3
            },
            repulse: {
              distance: 200,
              duration: 0.4
            },
            push: {
              particles_nb: 4
            },
            remove: {
              particles_nb: 2
            }
          }
        },
        retina_detect: true
      });

    }
  };
})(jQuery, Drupal);
