<template>
  <div>
    <!-- Title and subtitle -->
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        xl="10"
        align="center"
      >
        <h1>
          {{ $t('events') }}
        </h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col
        cols="12"
        style="margin-bottom: 0px!important; padding-bottom: 0px!important;"
      >
        <v-tabs
          v-model="modelTabs"
          background-color="primary"
          class="elevation-2"
          dark
          @change="redrawMap()"
        >
          <v-tab
            :href="`#tab-current`"
          >
            {{ $t('eventsAvailable') }}
          </v-tab>
          <v-tab
            :href="`#tab-passed`"
          >
            {{ $t('eventsPassed') }}
          </v-tab>

          <!-- Events available -->

          <v-tab-item
            :value="'tab-current'"
          >
            <v-card class="pa-6">
              <v-row>
                <v-col
                  cols="12"
                >
                  <v-card
                    v-show="loadingMap"
                    flat
                    align="center"
                    height="500"
                    color="backSpiner"
                  >
                    <v-progress-circular
                      size="250"
                      indeterminate
                      color="tertiary"
                    />
                  </v-card>
                  <v-card
                    v-if="$te('events.intro')"
                    elevation="0"
                  >
                    <v-card-text v-html="$t('events.intro')" />
                  </v-card>
                  <m-map
                    v-if="!loadingMap && !loading && pointsComingMap.length >= 0"
                    ref="mmap"
                    :points="pointsComingMap"
                    :provider="mapProvider"
                    :url-tiles="urlTiles"
                    :attribution-copyright="attributionCopyright"
                    :zoom="2"
                  />
                  <v-skeleton-loader
                    v-else
                    ref="skeleton"
                    type="card"
                    class="mx-auto"
                    width="100%"
                  />
                </v-col>
              </v-row>
              <v-card-title>
                <v-row>
                  <v-col
                    v-if="eventButtonDisplay"
                    cols="6"
                  >
                    <v-btn
                      type="button"
                      color="secondary"
                      rounded
                      :href="paths.event_create"
                    >
                      {{ $t('createEvent') }}
                    </v-btn>
                  </v-col>
                  <v-col
                    cols="6"
                  >
                    <div class="flex-grow-1" />

                    <v-card
                      class="ma-3 pa-6"
                      outlined
                      tile
                    >
                      <v-text-field
                        v-model="search"
                        hide-details
                        :label="$t('search')"
                        single-line
                        clearable
                        @input="updateSearch"
                      />
                    </v-card>

                    <!-- Community -->
                    <v-row
                      v-if="canSelectCommunity"
                      justify="center"
                    >
                      <v-col
                        cols="11"
                      >
                        <v-select
                          v-model="selectedCommunity"
                          :items="communities"
                          item-text="name"
                          return-object
                          :label="$t('filterByCommunity')"
                          single-line
                          clearable
                        />
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-card-title>
              <v-data-iterator
                :search="search"
                :items="eventscoming"
                :items-per-page.sync="itemsPerPage"
                :page="page"
                :server-items-length="totalItems"
                :no-data-text="$t('noEvent')"
                :footer-props="{
                  'items-per-page-options': itemsPerPageOptions,
                  'items-per-page-all-text': $t('all'),
                  'itemsPerPageText': $t('linePerPage')
                }"
                :loading="loading"
                @update:options="updateOptions"
              >
                <template>
                  <v-row v-if="loading">
                    <v-skeleton-loader
                      v-for="n in 3"
                      :key="n"
                      ref="skeleton"
                      type="list-item-avatar-three-line"
                      class="mx-auto"
                      width="100%"
                    />
                  </v-row>
                  <v-row v-else>
                    <v-col
                      v-for="item in eventscoming"
                      :key="item.index"
                      cols="12"
                      class="ma-3 pa-6"
                      outlined
                      tile
                    >
                      <EventListItem
                        :item="item"
                      />
                    </v-col>
                  </v-row>
                </template>
              </v-data-iterator>
            </v-card>
          </v-tab-item>

          <!-- Events passed -->
          <v-tab-item
            :value="'tab-passed'"
          >
            <v-card class="pa-6">
              <v-card-title>
                <v-row>
                  <v-col
                    cols="6"
                  >
                    <v-btn
                      v-if="eventButtonDisplay"
                      type="button"
                      color="secondary"
                      rounded
                      :href="paths.event_create"
                    >
                      {{ $t('createEvent') }}
                    </v-btn>
                  </v-col>
                  <v-col
                    cols="6"
                  >
                    <div class="flex-grow-1" />
                    <v-card
                      class="ma-3 pa-6"
                      outlined
                      tile
                    >
                      <v-text-field
                        v-model="searchPassed"
                        hide-details
                        :label="$t('search')"
                        single-line
                        clearable
                        @input="updateSearchPassed"
                      />
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-title>
              <v-data-iterator
                :search="searchPassed"
                :items="eventspassed"
                :items-per-page.sync="itemsPerPage"
                :page="pagePassed"
                :server-items-length="totalItemsPassed"
                :footer-props="{
                  'items-per-page-options': itemsPerPageOptions,
                  'items-per-page-all-text': $t('all'),
                  'itemsPerPageText': $t('linePerPage')
                }"
                :loading="loading"
                @update:options="updateOptionsPassed"
              >
                <template>
                  <v-row v-if="loading">
                    <v-skeleton-loader
                      v-for="n in 3"
                      :key="n"
                      ref="skeleton"
                      type="list-item-avatar-three-line"
                      class="mx-auto"
                      width="100%"
                    />
                  </v-row>
                  <v-row v-else>
                    <v-col
                      v-for="item in eventspassed"
                      :key="item.index"
                      cols="12"
                      class="ma-3 pa-6"
                      outlined
                      tile
                    >
                      <EventListItem
                        :item="item"
                      />
                    </v-col>
                  </v-row>
                </template>
              </v-data-iterator>
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import maxios from "@utils/maxios";
import debounce from "lodash/debounce";
import moment from "moment";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/event/EventList/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/event/EventList/";
import MMap from "@components/utilities/MMap/MMap"
import L from "leaflet";
import EventListItem from "@components/event/EventListItem";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  components:{
    EventListItem,MMap
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  props:{
    paths: {
      type: Object,
      default: null
    },
    mapProvider:{
      type: String,
      default: ""
    },
    urlTiles:{
      type: String,
      default: ""
    },
    attributionCopyright:{
      type: String,
      default: ""
    },
    itemsPerPageDefault: {
      type: Number,
      default: 1
    },
    isLogged: {
      type: Boolean,
      default: false
    },
    tabDefault: {
      type: String,
      default: ""
    },
    eventButtonDisplay:{
      type: Boolean,
      default:false
    },
    canSelectCommunity: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      search: '',
      searchPassed : '',
      itemsPerPageOptions: [1,10, 20, 50, 100],
      itemsPerPage: this.itemsPerPageDefault,
      itemsPerPagePassed: this.itemsPerPageDefault,
      page:1,
      pagePassed:1,
      headers: [
        {
          text: 'Id',
          align: 'left',
          sortable: false,
          value: 'id',
        },
        { text: 'Nom', value: 'name' },
        { text: 'Description', value: 'description' },
        { text: 'Image', value: 'logos' }
      ],
      loading: false,
      loadingMap: false,
      pointsComingMap : [],
      eventscoming:[],
      eventspassed:[],
      pointsComing:[],
      totalItems:0,
      totalItemsPassed:0,
      modelTabs:(this.tabDefault !== "") ? this.tabDefault : "tab-current",
      selectedCommunity: null,
      communities: [],
    }
  },
  watch:{
    pointsComing(){
      this.createMapComing();
    },
    selectedCommunity() {
      this.getEvents(true);
    },
  },
  mounted(){
    maxios
      .post(this.$t('getCommunities'))
      .then(response => {
        if(response.data.communities){
          this.communities = response.data.communities;
        }
        this.loading = false;

      })
      .catch(function (error) {
      });
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{
    searchChanged: function (search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.dataRegular = search.regular;
      this.date = search.date;
    },
    post: function (path, params, method='post') {
      const form = document.createElement('form');
      form.method = method;
      form.action = window.location.origin+'/'+path;

      for (const key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    buildPoint: function(e,lat,lng,title="",pictoUrl="",size=[],anchor=[]){
      let point = {
        title:title,
        popup : this.buildPopup(e),
        latLng:L.latLng(lat, lng),
        icon: {}
      }

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }
      return point;
    },
    buildPopup : function(evt){
      let popup = {
        title : evt.name,
        images : evt.images,
        description  : evt.fullDescription,
        date_begin   : this.$t('startEvent') +' : '+  this.computedDateFormat(evt.fromDate.date,evt.useTime),
        date_end   : this.$t('endEvent') +' : '+ this.computedDateFormat(evt.toDate.date,evt.useTime),
        linktoevent  : this.$t('routes.event', {id:evt.id, urlKey:evt.urlKey})
      };
      return popup;

    },
    createMapComing () {
      this.loadingMap = true;
      this.pointsComingMap.length = 0;

      if(this.pointsComing != null){
        this.pointsComing.forEach((waypoint, index) => {
          if (waypoint.latLng.lat && waypoint.latLng.lon) {
            this.pointsComingMap.push(this.buildPoint(waypoint.event,waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
          }
        });
        this.loadingMap = false;
        this.redrawMap();
      }
    },
    computedDateFormat(date, withTime = false) {
      if (withTime) {
        return moment(date).format("DD/MM/YYYY HH:mm");
      }
      return moment(date).format("DD/MM/YYYY");
    },
    getEvents(coming){
      this.loading = true;
      let params = {
        'coming':coming,
        'perPage':(coming) ? this.itemsPerPage : this.itemsPerPagePassed,
        'page': (coming) ? this.page : this.pagePassed,
        'showAllEvents':true,
        'search':{
          'name':this.search
        },
        'searchPassed':{
          'name':this.searchPassed
        },
        'communityId':this.selectedCommunity ? this.selectedCommunity.id : null
      }
      maxios
        .post(this.$t('routes.getList'),params)
        .then(response => {
          if(response.data.eventComing){
            this.eventscoming = response.data.eventComing;
            this.totalItems = response.data.totalItems;
            params.page = 1;
            params.perPage = 9999;
            maxios
              .post(this.$t('routes.getList'), params)
              .then(responsePoints => {
                this.pointsComing = responsePoints.data.points;
              });
          }
          if(response.data.eventPassed){
            this.eventspassed = response.data.eventPassed;
            this.totalItemsPassed = response.data.totalItems;

          }
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
        });
    },
    updateOptions(data){
      this.itemsPerPage = data.itemsPerPage;
      this.page = data.page;
      this.getEvents(true);
    },
    updateOptionsPassed(data){
      this.itemsPerPagePassed = data.itemsPerPage;
      this.pagePassed = data.page;
      this.getEvents(false);
    },
    redrawMap(){
      setTimeout(() => {
        if(this.$refs.mmap !== undefined){this.$refs.mmap.redrawMap()}
      },500);
    },
    updateSearch: debounce(function(value) {
      this.page=1;
      this.getEvents(true);
    }, 1000),
    updateSearchPassed: debounce(function(value) {
      this.pagePassed=1;
      this.getEvents(false);
    }, 1000)
  }
}
</script>

<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>
