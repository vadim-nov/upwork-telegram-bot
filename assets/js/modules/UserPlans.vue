<template>
    <div>
        <div class="row">
            <div class="col-lg-2 col-md-6 m-15px-tb">
            </div>
            <div v-for="plan in plans" class="col-lg-4 col-md-6 m-15px-tb">
                <div v-bind:class="{ 'price-table': true, 'current': plan.isCurrent }">
                    <div class="icon">
                        <img :src="plan.pic" title="" alt="">
                    </div>
                    <div class="pt-head">
                        <h6 class="dark-color font-alt">{{ plan.name }}</h6>
                        <div class="pt-price theme-color">{{plan.price}}<span>/Month</span>
                        </div>
                    </div>
                    <div class="pt-body">
                        <ul>
                            <li>{{ plan.searchCount }} searches</li>
                            <li class="pt-no">{{ plan.subTitle }}</li>
                        </ul>
                    </div>
                    <div class="pt-btn">
                        <div v-if="plan.isCurrent">
                            <button disabled
                                    class="m-btn">
                                Current plan
                            </button>
                        </div>
                        <div v-else>
                            <button :disabled="isLoading" class="m-btn" v-bind:class="{'m-btn-theme': !isLoading }"
                                    @click="upgrade(plan.name)">Choose
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <modal name="plan-payment-modal">
            <div v-if="order.id" style="padding: 10px">
                <h4><b>Upgrade to: </b> {{ order.plan }}</h4>
                <p><b>Order Ref:</b> {{ order.id }}</p>
                <p><b>Status:</b> {{ order.isPaid ? 'Paid': 'Not paid' }}</p>
                <hr/>
                <a class="btn btn-primary" :href="order.url">Pay {{ order.price }}</a>
            </div>
        </modal>
    </div>
</template>

<script>
  import LoaderSet from './LoaderSet';
  import {mapState} from 'vuex';
  import store from './../store';

  export default {
    components: {LoaderSet},
    store: store,
    computed: mapState(['order', 'isLoading', 'plans']),
    methods: {
      upgrade(plan) {
        this.$store.dispatch('upgrade', plan).then(() => {
          this.$modal.show('plan-payment-modal');
        });
      },
    },
    async mounted() {
      await this.$store.dispatch('loadPlans');
    },
  };
</script>

<style lang="scss">
    @import "./../../css/variable";
    @import "./../../css/mixin";

    .price-table {
        text-align: center;
        background: $px-white;
        border-radius: 5px;
        @include transition(ease all 0.55s);
        box-shadow: 0px 15px 38px rgba($px-black, 0.1);

        &.current {

            border: 2px solid #ffda18;
        }

        .icon {
            width: 110px;
            height: 110px;
            margin-top: 30px;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
            position: relative;
            background: $px-gray;
            border-radius: 50%;
            overflow: hidden;

            img {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                max-width: 70%;
                max-height: 70%;
                margin: auto;
            }

        }

        h6 {
            font-weight: 600;
            font-size: 17px;
            margin: 0 0 12px;
            padding: 0 0 13px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;

            &:after {
                content: "";
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                width: 40px;
                height: 2px;
                background: $px-theme;
                margin: auto;
            }

        }

        .pt-price {
            font-size: 30px;
            font-weight: 600;
            margin-bottom: 5px;

            span {
                font-weight: 400;
                font-size: 14px;
            }

        }

        .pt-btn {
            padding: 30px 0 40px;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;

            li {
                padding: 4px 0;

                b {
                    font-weight: normal;
                }

            }

        }

        .pt-body {
            padding: 0 20px;
        }

        &.active {
            position: relative;
            z-index: 1;
            padding: 30px 0 30px;
        }

    }
</style>
