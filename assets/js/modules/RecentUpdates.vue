<template>
    <div>
        <transition
                v-on:before-enter="beforeEnter"
                v-on:enter="enter"
                v-on:leave="leave"
                v-bind:css="false"
        >
            <div class="stream" v-if="currentJob">
                <div class="stream-container">
                    <div class="stream__img"><img src="/static/img/stream-bot.png" alt="Upworkee bot"></div>
                    <div class="stream__content">
                        <div class="stream__content-title"><span class="title">Upworkee Bot</span><span class="time">{{getUserTime()}}</span>
                        </div>
                        <div class="stream__content-data">
                            <div><span>Title: </span>{{formattedTitle}}</div>
                            <div><span>Posted on: </span>{{formattedPostedDate}}</div>
                            <div><span>Description: </span>{{formattedDescription}}</div>
                        </div>
                        <a :href="jobLink" class="btn-apply">Click to apply</a>
                    </div>
                </div>
            </div>
        </transition>
        <img v-if="newJob" src="/static/upworkee-action.gif" title="" alt="">
        <img v-else src="/static/upworkee-idle.gif" title="" alt="">
    </div>
</template>

<script>
  import axios from 'axios';
  import moment from 'moment';
  import Velocity from 'velocity-animate';

  export default {
    data: function() {
      return {
        intervals: [],
        jobsToShow: [],
        shownJobs: [],
        currentJob: null,
        newJob: false,
        show: false,
      };
    },
    computed: {
      jobLink: function() {
        return this.currentJob.link;
      },
      formattedTitle: function() {
        return this.currentJob.cleanedTitle.length > 50
            ? this.currentJob.cleanedTitle.substring(0, 50) + '...'
            : this.currentJob.cleanedTitle;
      },
      formattedPostedDate: function() {
        return moment(this.currentJob.pubDate).format('DD/MM/YYYY h:mm a');
      },
      formattedDescription: function() {
        return this.currentJob.cleanedDescription.substring(0, this.currentJob.cleanedDescription.indexOf('<b>Posted On')).
                substring(0, 100).
                substring(0, 100).
                replace(/<\/?[^>]+(>|$)/g, '')
            + '...';
      },
    },
    methods: {
      loadData: async function() {
        const response = await axios.get('/recent-jobs');
        this.jobsToShow = response.data;
      },
      getUserTime: function() {
        const date = new Date();
        return date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
      },
      initTodo: function() {
        this.intervals['todo'] = setInterval(() => {
          if (this.jobsToShow.length) {
            this.shownJobs.push(this.jobsToShow.pop());
            this.currentJob = this.shownJobs[this.shownJobs.length - 1];
            this.handleNewJobUpdate();
          }
        }, 4000);
      },
      handleNewJobUpdate: function() {
        this.newJob = true;
        this.intervals['handleNewJobUpdate'] = setTimeout(() => {
          if (this.newJob) {
            this.newJob = false;
            this.currentJob = null;
          }
        }, 2000);
      },
      beforeEnter: function(el) {
        Velocity(el, {
          translateY: '150px',
          opacity: 0,
        });
      },
      enter: function(el, done) {
        Velocity(el, {opacity: 1, translateY: '0px'}, {duration: 300});
        Velocity(el, {complete: done});
      },
      leave: function(el, done) {
        Velocity(el, {translateY: '-150px', opacity: 0}, {duration: 800});
        Velocity(el, {complete: done});
      },
    },

    async mounted() {
      await this.loadData();
      this.currentJob = this.jobsToShow.pop();
      this.handleNewJobUpdate();
      this.initTodo();
      this.intervals['loadData'] = setInterval(this.loadData, 90000); // 90 seconds
    },
    beforeDestroy() {
      for (let intervalName of Object.keys(this.intervals)) {
        clearInterval(this.intervals[intervalName]);
      }
    },
  };
</script>

<style lang="scss">
    .stream {
        background: #FFFFFF;
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
        border-radius: 8px;
        padding: 16px;
        font-family: "Open Sans", sans-serif;
        font-size: 14px;
        line-height: 1.4;
        color: #000;
        margin-top: 32px;
        position: absolute;
        top: -45%;
        left: 50px;
        width: 100%;
        max-width: 492px;
        z-index: 1;

        &-container {
            display: flex;
            flex-flow: row nowrap;
        }

        &__img {
            display: flex;
            align-items: flex-start;
            min-width: 56px;

            img {
                width: 56px;
                height: 56px;
            }
        }

        &__content {
            display: flex;
            flex-flow: column nowrap;
            padding-left: 16px;
            width: 100%;

            &-title {
                display: flex;
                flex-flow: row nowrap;
                justify-content: space-between;
                margin-bottom: 8px;

                .title {
                    color: #1EA8FF;
                }

                .time {
                    color: #A0ACB6;
                }
            }

            &-data {

                span {
                    font-weight: bold;
                }

                div {
                    margin-bottom: 8px;
                }
            }

            .btn-apply {
                padding: 8px;
                font-weight: bold;
                border: 1px solid #1EA8FF;
                border-radius: 8px;
                align-self: flex-start;
                color: #000;
            }
        }

        @media screen and (max-width: 991px) {
            left: 0;
            margin: 0 10px;
        }

        @media screen and (max-width: 420px) {

        }
    }
</style>
