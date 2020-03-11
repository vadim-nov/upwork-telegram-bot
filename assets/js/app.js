// /*
//  * Welcome to your app's main JavaScript file!
//  *
//  * We recommend including the built version of this JavaScript file
//  * (and its CSS file) in your base layout (base.html.twig).
//  */
//
// // any CSS you require will output into a single css file (app.css in this case)
require('./../css/app.scss');
window.$ = window.jQuery = require('jquery');
require('jquery.appear');
require('./jquery.parallax-scroll');
require('./jquery.countTo');

import Vue from 'vue';
import VModal from 'vue-js-modal';
import RecentUpdatesWidget from './modules/RecentUpdates';
import ClientCabinet from './modules/ClientCabinet';
import SubscribeWidget from './modules/SubscribeWidget';
import ContactForm from './modules/ContactForm';

Vue.use(VModal);
Vue.config.productionTip = false;

const recentUpdatesEP = document.getElementById('recent-updates-mountpoint');
const clientCabinetEP = document.getElementById('client-cabinet-mountpoint');
const contactFormEP = document.getElementById('contactform-mountpoint');
const subscribeWidgetMP = document.getElementById(
    'subscribe-widget-mountpoint');
if (recentUpdatesEP)
  new Vue({
    render: h => h(RecentUpdatesWidget),
  }).$mount(recentUpdatesEP);
if (clientCabinetEP) {
  new Vue({
    render: h => h(ClientCabinet),
  }).$mount(clientCabinetEP);
}
if (subscribeWidgetMP) {
  new Vue({
    render: h => h(SubscribeWidget),
  }).$mount(subscribeWidgetMP);
}
if (contactFormEP) {
  new Vue({
    render: h => h(ContactForm),
  }).$mount(contactFormEP);
}

(function($) {
  'use strict';

  $(document).ready(function() {
    let $counter = $('.counter');
    if ($counter.length > 0) {
      $counter.each(function() {
        $(this).appear(function() {
          $(this).find('.count').countTo({
            speed: 1500,
            refreshInterval: 10,
          });
        });
      });
    }
  });
  $(window).on('scroll', function() {
    const HscrollTop = $(window).scrollTop();
    if (HscrollTop >= 100) {
      $('header').addClass('fixed-header');
    } else {
      $('header').removeClass('fixed-header');
    }
  });
  $('a.smooth-scroll').click(function() {
    const elementClick = $(this).attr('href');
    const destination = $(elementClick).offset().top;
    $('html, body').animate({scrollTop: destination}, 600);
    return false;
  });
})(jQuery);
