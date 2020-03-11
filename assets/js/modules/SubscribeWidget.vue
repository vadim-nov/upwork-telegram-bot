<template>
    <div>
        <div class="contact-form">
            <form method="post" @submit="handleSubscribe">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <input v-model="email" name="name" type="email" placeholder="Your email address"
                                   class="form-xl form-control email" required>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div v-if="!isSubscribed && !isLoading" class="send">
                            <button class="m-btn m-btn-t-theme" type="submit" name="send">
                                Subscribe
                            </button>
                        </div>
                        <span class="dark-color font-alt" v-else>
                               Thanks!
                            </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
  import {callController} from './../api';

  export default {
    data: function() {
      return {
        email: null,
        isLoading: false,
        isSubscribed: false,
      };
    },

    methods: {
      async handleSubscribe(e) {
        e.preventDefault();
        if (this.email) {
          this.isLoading = true;
          await callController('/subscribe', 'POST', JSON.stringify({email: this.email}));
          this.isLoading = false;
          this.isSubscribed = true;
          setInterval(() => {
            this.isSubscribed = false;
            this.email = null;
          }, 4000);
        }
      },
    },
  };
</script>

<style lang="scss" scoped>
    @import "./../../css/variable";
    @import "./../../css/mixin";

    .contact-form {
        padding: 8%;

        .form-group {
            margin-bottom: 25px;

            label {
                font-weight: 600;
                font-size: 12px;
                text-transform: uppercase;
            }

            .form-control {
                border-radius: 0;
                font-size: 14px;
                box-shadow: none !important;

                &.email {
                    height: 52px;
                    border-radius: 3px;
                }

                &:focus {
                    border-color: $px-theme;
                }

                &:not(textarea) {
                    height: 45px;
                }

            }

            textarea.form-control {
                height: 150px;
            }

        }

    }
</style>
