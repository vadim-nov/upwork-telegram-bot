import Vue from 'vue';
import Vuex from 'vuex';
import {getJwt, callApi} from './api';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    searches: [],
    isLoading: false,
    order: {
      id: null,
      url: null,
      price: null,
      isPaid: null,
      plan: null,
    },
    plans: {
      /**
       * url
       * id
       * price
       * pic
       * subTitle
       * updateFrequency
       * isPaid
       * isCurrent
       */
    },
  },
  mutations: {
    enableLoading(state) {
      state.isLoading = true;
    },
    disableLoading(state) {
      state.isLoading = false;
    },
    loadSearches(state, searches) {
      state.searches = searches;
    },
    createOrder(state, order) {
      state.order = order;
    },
    loadPlans(state, data) {
      state.plans = data.map(plan => {
        if (plan.name === 'Premium') {
          plan.pic = '/static/img/scooter.svg';
          plan.subTitle = 'Every minute update';
        } else {
          plan.pic = '/static/img/car.svg';
          plan.subTitle = 'Every 5 mins update';

        }
        return plan;
      });
    },
  },
  actions: {
    async removeSearch({commit, dispatch}, id) {
      commit('enableLoading');
      await callApi(`/api/user_searches/${id}`, 'DELETE');
      dispatch('loadSearches');
    },

    async addSearch({commit, dispatch}, data) {
      commit('enableLoading');
      const res = await callApi('/api/user_searches', 'POST', data);
      if (res.error) {
        commit('disableLoading');
      } else {
        dispatch('loadSearches');
      }
      return res;
    },

    async loadSearches({commit}) {
      commit('enableLoading');
      if (getJwt) {
        const {error, data} = await callApi('/api/user_searches', 'GET');
        commit('loadSearches', data['hydra:member']);
      }
      commit('disableLoading');
    },

    async upgrade({commit}, plan) {
      commit('enableLoading');
      const response = await callApi('/api/orders', 'POST', `{
        "planName": "${plan}"
      }`);
      if (!response.error) {
        commit('createOrder', response.data);
      }
      commit('disableLoading');

      return response;
    },

    async loadPlans({commit}) {
      commit('enableLoading');
      const {data} = await callApi('/api/plans', 'GET');
      commit('loadPlans', data['hydra:member']);
      commit('disableLoading');
    },
  },
});

