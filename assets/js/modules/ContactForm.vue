<template>
    <form class="contactform" method="post" @submit="handleSubscribe">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <input v-model="name" name="contactform_name" type="text" placeholder="Name"
                           class="form-xl form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <input v-model="email" type="contactform_email" placeholder="Email" name="email" required
                           class="form-xl form-control">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                                        <textarea v-model="body" placeholder="Your Comment" name="contactform_message"
                                                  class="form-xl form-control" required></textarea>
                </div>
            </div>
            <div class="col-md-12">
                <div class="send">
                    <transition name="fade">
                        <button v-show="!isContacted" class="m-btn m-btn-t-theme" type="submit"
                                name="send">Get in touch
                        </button>
                    </transition>
                    <transition name="fade">
                        <h4 v-if="isContacted">Thanks for your feedback!</h4>
                    </transition>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
  import {callController} from './../api';

  export default {
    name: 'ContactForm',
    data: function() {
      return {
        email: null,
        name: null,
        body: null,
        isContacted: false,
      };
    },
    methods: {
      async handleSubscribe(e) {
        e.preventDefault();
        if (this.body) {
          this.isContacted = true;
          await callController('/contact', 'POST',
              JSON.stringify({email: this.email, name: this.name, body: this.body}));
          this.email = this.name = this.body = null;

          setInterval(() => {
            this.isContacted = false;
          }, 4000);
        }
      },
    },
  };
</script>

<style scoped lang="scss">
    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active до версии 2.1.8 */ {
        opacity: 0;
    }
</style>
