<template>
  <div>
    <!-- Lister les evenements de l'user
   <v-row>
     <v-col
       cols="12"
       style="margin-bottom: 0px!important; padding-bottom: 0px!important;"
     >
       <v-toolbar
         flat
         color="primary"
         dark
       >
         <v-toolbar-title> {{ $t('myCommunities') }}</v-toolbar-title>
       </v-toolbar>
       <v-card class="pa-6">
         <v-data-iterator
           :items="communitiesUser"
           :items-per-page.sync="itemsPerPage"
           :footer-props="{
             'items-per-page-options': itemsPerPageOptions,
             'items-per-page-all-text': $t('all'),
             'itemsPerPageText': $t('linePerPage')
           }"
         >
           <template>
             <v-row>
               <v-col
                 v-for="item in communitiesUser"
                 :key="item.index"
                 cols="12"
                 class="ma-3 pa-6"
                 outlined
                 tile
               >
                 <CommunityListItem :item="item" />
               </v-col>
             </v-row>
           </template>
         </v-data-iterator>
       </v-card>
     </v-col>
   </v-row>

   -->
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
          background-color="primary"
          class="elevation-2"
          dark
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
                  <m-map
                    v-show="!loadingMap"
                    ref="mmap"
                    :points="pointsComingMap"
                    :provider="mapProvider"
                    :url-tiles="urlTiles"
                    :attribution-copyright="attributionCopyright"
                  />
                </v-col>
              </v-row>
              <v-card-title>
                <v-row>
                  <v-col
                    cols="6"
                  >
                    <a :href="paths.event_create">
                      <v-btn
                        type="button"
                        color="secondary"
                        rounded
                      >
                        {{ $t('createEvent') }}
                      </v-btn>
                    </a>
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
                      />
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-title>
              <v-data-iterator
                :search="search"
                :items="eventscoming"
                :items-per-page.sync="itemsPerPage"
                :footer-props="{
                  'items-per-page-options': itemsPerPageOptions,
                  'items-per-page-all-text': $t('all'),
                  'itemsPerPageText': $t('linePerPage')
                }"
              >
                <template>
                  <v-row>
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
                    <a :href="paths.event_create">
                      <v-btn
                        type="button"
                        color="secondary"
                        rounded
                      >
                        {{ $t('createEvent') }}
                      </v-btn>
                    </a>
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
                      />
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-title>
              <v-data-iterator
                :search="searchPassed"
                :items="eventspassed"
                :items-per-page.sync="itemsPerPage"
                :footer-props="{
                  'items-per-page-options': itemsPerPageOptions,
                  'items-per-page-all-text': $t('all'),
                  'itemsPerPageText': $t('linePerPage')
                }"
              >
                <template>
                  <v-row>
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

import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/event/EventList.json";
import TranslationsClient from "@clientTranslations/components/event/EventList.json";
import MMap from "@components/utilities/MMap"
import L from "leaflet";
import EventListItem from "@components/event/EventListItem";


let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components:{
    EventListItem,MMap
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    eventscoming: {
      type: Array,
      default: null
    },
    eventspassed : {
      type: Array,
      default: null
    },
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
    pointsComing: {
      type: Array,
      default: null
    },
  },
  data () {
    return {
      locale: this.$i18n.locale,
      search: '',
      searchPassed : '',
      itemsPerPageOptions: [10, 20, 50, 100, -1],
      itemsPerPage: 10,
      headers: [
        {
          text: 'Id',
          align: 'left',
          sortable: false,
          value: 'id',
        },
        { text: 'Nom', value: 'name' },
        { text: 'Description', value: 'fulldescription' },
        { text: 'Image', value: 'logos' }
      ],
      loadingMap: false,
      errorUpdate: false,
      pointsComingMap : [],
    }
  },
  mounted() {
    this.createMapComing();
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
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
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
        titre : evt.name,
        images : evt.images,
        description  : evt.fullDescription,
        date_begin   : this.$t('startEvent') +' : '+  this.computedDateFormat(evt.fromDate.date),
        date_end   : this.$t('endEvent') +' : '+ this.computedDateFormat(evt.toDate.date),
        linktoevent  : this.$t('routes.event', {id:evt.id})
      };
      return popup;

    },
    createMapComing () {
      const self = this;
      this.errorUpdate =200;
      this.loadingMap = true;
      this.pointsComingMap.length = 0;

      if(this.pointsComing != null){
        this.pointsComing.forEach((waypoint, index) => {
          this.pointsComingMap.push(this.buildPoint(waypoint.event,waypoint.latLng.lat,waypoint.latLng.lon,waypoint.title));
        });
        this.loadingMap = false;
        setTimeout(() => {
          self.$refs.mmap.redrawMap()
        },500);
      }
    },computedDateFormat(date) {
      // moment.locale(this.locale);
      // return this.date
      //   ? moment(this.date).format(this.$t("ui.i18n.date.format.fullDate"))
      //   : null;
      return moment(date).format("DD/MM/YYYY hh:mm");
    }

  }
}
</script>

<style lang="scss" scoped>
a{
    text-decoration: none;
}
</style>