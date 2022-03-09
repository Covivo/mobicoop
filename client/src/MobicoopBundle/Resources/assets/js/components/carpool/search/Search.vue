<template>
  <v-main>
    <v-container
      v-if="!horizontal"
      grid-list-md
      text-xs-center
    >
      <v-row
        justify="center"
      >
        <v-col
          
          cols="12"
          :xl="fullSize ? 12 : results ? 12 : 6"
          :lg="fullSize ? 12 : results ? 12 : 6"
          :md="fullSize ? 12 : results ? 12 : 6"
        >
          <!--SearchJourney-->
          <search-journey
            :geo-search-url="geoSearchUrl"
            :user="user"
            :init-regular="dataRegular"
            :init-destination="defaultDestination"
            :init-origin="defaultOrigin"
            :punctual-date-optional="punctualDateOptional"
            :show-destination="showDestination"
            :iswidget="isWidget"
            :init-outward-date="defaultOutwardDate"
            :image-swap="imageSwap"
            :prioritize-relaypoints="prioritizeRelaypoints"
            @change="searchChanged"
          />
        </v-col>
      </v-row>

      <!-- Buttons -->
      <v-row
        v-if="!horizontal"
        justify="center"
      >
        <v-col
          :cols="fullSize ? 12 : 6"
        >
          <v-row>
            <v-col
              cols="12"
              md="6"
              :class="classAlignSearchButton"
            >
              <v-btn
                v-if="!hidePublish"
                :class="colorButton+' '+textColorButton"
                outlined
                rounded
                :loading="loadingPublish"
                @click="publish"
              >
                {{ $t('buttons.publish.label') }}
              </v-btn>
            </v-col>
            <v-col
              :class="classAlignSearchButton"
              cols="12"
              md="6"
            >
              <v-btn
                :disabled="searchUnavailable || disableSearch"
                :loading="loadingSearch"
                color="secondary"
                rounded
                min-width="150px"
                @click="search"
              >
                {{ $t('buttons.search.label') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
    <v-row v-else-if="horizontal">
      <v-col
          
        cols="12"
      >
        <search-journey-horizontal
          :geo-search-url="geoSearchUrl"
          :user="user"
          :init-regular="dataRegular"
          :punctual-date-optional="punctualDateOptional"
          :elevation="horizontalElevation"
          :prioritize-relaypoints="prioritizeRelaypoints"
          @change="searchChanged"
          @search="search"
        />
      </v-col>
    </v-row>
  </v-main>
</template>

<script>
import moment from "moment";
import {merge} from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/search/Search/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/carpool/search/Search/";
import SearchJourney from "@components/carpool/search/SearchJourney";
import SearchJourneyHorizontal from '@components/carpool/search/SearchJourneyHorizontal.vue';

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  components: {
    SearchJourney,
    SearchJourneyHorizontal
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    }, 
    regular: {
      type: Boolean,
      default: false
    }, 
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    // params to add to the publish and search routes
    params: {
      type: Object,
      default: null
    },
    defaultDestination: {
      type: Object,
      default: null
    },
    defaultOrigin: {
      type: Object,
      default: null
    },
    disableSearch: {
      type: Boolean,
      default: false
    },
    hidePublish: {
      type: Boolean,
      default: false
    },
    showDestination: {
      type: Boolean,
      default: true
    },
    isWidget: {
      type: Boolean,
      default: false
    },
    results: {
      type: Boolean,
      default: false
    },
    defaultOutwardDate: {
      type: String,
      default: null
    },
    fullSize:{
      type: Boolean,
      default:false
    },
    classAlignPublishButton:{
      type: String,
      default:"text-left"
    },
    classAlignSearchButton:{
      type: String,
      default:"text-left"
    },
    imageSwap:{
      type:String,
      default:""
    },
    publishButtonAlwaysActive:{
      type: Boolean,
      default:false
    },
    horizontal:{
      type: Boolean,
      default: false
    },
    horizontalElevation:{
      type:Number,
      default: 2
    },
    prioritizeRelaypoints: {
      type: Boolean,
      default: false
    },
    colorButton: {
      type:String,
      default: null
    },
    textColorButton: {
      type:String,
      default: null
    }
  },
  data() {
    return {
      loadingSearch: false,
      loadingPublish: false,
      logged: this.user ? true : false,
      dataRegular: this.regular,
      date: this.defaultOutwardDate,
      time: null,
      origin: this.defaultOrigin,
      destination: this.defaultDestination,
      locale: localStorage.getItem("X-LOCALE")
    };
  },
  computed: {
    searchUnavailable() {
      return (!this.origin || !this.destination || (!this.dataRegular && !this.date && !this.punctualDateOptional))
    },
    dateFormated() {
      return this.date
        ? moment(this.date).format(this.$t("urlDate"))
        : null;
    },
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    post: function (path, params, method='post') {
      const form = document.createElement('form');
      form.method = method;
      if (this.isWidget) {
        form.target = '_blank';
      }
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
      this.loadingSearch= false;
    },
    searchChanged: function (search) {
      this.origin = search.origin;
      this.destination = search.destination;
      this.dataRegular = search.regular;
      this.date = search.date;
    },
    search: function () {
      this.loadingSearch = true;
      let lParams = {
        origin: JSON.stringify(this.origin),
        destination: JSON.stringify(this.destination),
        regular:this.dataRegular,
        date:this.date?this.date:null,
        time:this.time?this.time:null,
        ...this.params
      };
      this.post(`${this.$t("buttons.search.route")}`, lParams);
    },
    publish: function () {
      if (this.logged){
        this.loadingPublish = true;
        let lParams = {
          origin: JSON.stringify(this.origin),
          destination: JSON.stringify(this.destination),
          regular:this.dataRegular,
          date:this.date?this.date:null,
          time:this.time?this.time:null,
          ...this.params
        };
        this.post(`${this.$t("buttons.publish.route")}`, lParams);
      }else{
        window.location.href=this.$t("buttons.shareAnAd.route");
      }
    },
  },
};
</script>
