<template>
    <div>
        <div v-if="isLoading">
            <LoaderSet></LoaderSet>
        </div>
        <div v-else>
            <div v-if="searches">
                <h3 class="dark-color">Searches:</h3>

                <div class="row" v-for="(item) in searches">

                    <div class="col-2">{{ item.searchName }}</div>
                    <div class="col-6"><i>{{ item.searchUrl }}</i></div>
                    <div class="col-3 btn-delete">
                        <a style="color: #f75c5c" href="#" @click="removeSearch(item.id)">
                            Remove search
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <button @click="toggleModal" class="btn btn-success">+ search</button>
        <UserPlans></UserPlans>
        <modal name="add-search-modal">
            <form class="add-search-form" @submit="addSearch">
                <div class="form-group">
                    <label for="form_searchUrl" class="required">Keyword or upwork url</label>
                    <div class="input-group mb-3">
                        <input v-model="searchUrl" placeholder="Example: design or https://www.upwork.com/search/jobs/?q=react"
                               type="text"
                               id="form_searchUrl" class="form-control" aria-describedby="basic-addon3">
                    </div>
                    <div v-show="violationList['searchUrl']" class="invalid-feedback">
                        {{ violationList['searchUrl'] }}
                    </div>
                </div>
                <div class="form-group">
                    <label for="form_name">Name</label>
                    <input type="text"
                           v-model="searchName"
                           id="form_name"
                           name="form[name]"
                           placeholder="e.g. react"
                           class="form-control"/>
                    <div v-show="violationList['searchName']" class="invalid-feedback">
                        {{ violationList['searchName'] }}
                    </div>

                </div>
                <div v-if="errorText" class="alert alert-danger mb-3">
                    {{errorText}}
                </div>
                <button class="btn btn-success">Create</button>
            </form>
        </modal>
    </div>
</template>

<script>
  import store from './../store';
  import {mapState} from 'vuex';
  import LoaderSet from './LoaderSet';
  import UserPlans from './UserPlans';

  export default {
    components: {UserPlans, LoaderSet},
    data: function() {
      return {
        violationList: {},
        errorText: null,
        searchUrl: '',
        searchName: '',
      };
    },
    store: store,
    computed: {
      ...mapState(['isLoading', 'searches']),
    },
    methods: {
      addSearch(e) {
        e.preventDefault();

        this.$store.dispatch('addSearch', {
          searchName: this.searchName,
          searchUrl: this.searchUrl,
        }).then(res => {
          const {error} = res;
          if (error) {
            if (error['violations']) {
              for (let violation of  error['violations']) {
                this.$set(this.violationList, violation.propertyPath, violation.message);
              }
            } else {
              this.errorText = error['hydra:title'];
            }
          } else {
            this.errorText = null;
            this.$delete(this.violationList);
            this.searchUrl = this.searchName = '';
            this.$modal.hide('add-search-modal');
          }
        });
      },
      toggleModal() {
        this.$modal.show('add-search-modal');
      },
      async removeSearch(id) {
        if (confirm('Are you sure you want to delete this item?')) {
          await this.$store.dispatch('removeSearch', id);
        }
      },
    },
    async mounted() {
      await this.$store.dispatch('loadSearches');
    },
  };
</script>

<style lang="scss">
    .invalid-feedback {
        display: block;
    }

    .add-search-form {
        padding: 8px;
    }
</style>
