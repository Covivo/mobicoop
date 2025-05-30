<template>
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
          :init-origin="origin"
          :punctual-date-optional="punctualDateOptional"
          :show-destination="showDestination"
          :iswidget="isWidget"
          :init-outward-date="dateFormated"
          :init-outward-time="time"
          :image-swap="imageSwap"
          :geo-complete-results-order="geoCompleteResultsOrder"
          :geo-complete-palette="geoCompletePalette"
          :geo-complete-chip="geoCompleteChip"
          :date-time-picker="dateTimePicker"
          :switch-color="switchColor"
          :init-role="role"
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
        <div
          v-if="fullSize"
          class="d-flex flex-row justify-space-around"
        >
          <v-btn
            v-if="!hidePublish"
            :class="colorButton+' '+textColorButton"
            outlined
            rounded
            :loading="loadingPublish"
            :aria-label="$t('buttons.publish.label')"
            @click="publish"
          >
            {{ $t('buttons.publish.label') }}
          </v-btn>

          <v-btn
            :disabled="searchUnavailable || disableSearch"
            :loading="loadingSearch"
            :class="searchButtonClass"
            rounded
            min-width="150px"
            tabindex="0"
            :aria-label="$t('buttons.search.label')"
            @click="search"
          >
            {{ $t('buttons.search.label') }}
          </v-btn>
        </div>
        <v-row v-else>
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
              :aria-label="$t('buttons.publish.label')"
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
              :class="searchButtonClass"
              rounded
              min-width="150px"
              tabindex="0"
              :aria-label="$t('buttons.search.label')"
              @click="search"
            >
              {{ $t('buttons.search.label') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
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
          :geo-complete-results-order="geoCompleteResultsOrder"
          :geo-complete-palette="geoCompletePalette"
          :geo-complete-chip="geoCompleteChip"
          @change="searchChanged"
          @search="search"
        />
      </v-col>
    </v-row>
  </v-container>
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
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
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
    },
    dateTimePicker: {
      type: Boolean,
      default: false
    },
    defaultOutwardTime: {
      type: String,
      default: null
    },
    searchButtonClass: {
      type:String,
      default: 'secondary'
    },
    switchColor: {
      type: String,
      default: 'secondary'
    },
    defaultRoleToPublish: {
      type: Number,
      default:null
    }
  },
  data() {
    return {
      loadingSearch: false,
      loadingPublish: false,
      logged: this.user ? true : false,
      dataRegular: this.regular,
      date: this.defaultOutwardDate,
      time: this.defaultOutwardTime,
      destination: this.defaultDestination,
      origin: this.determineOrigin(),
      locale: localStorage.getItem("X-LOCALE"),
      role: this.defaultRoleToPublish ? this.defaultRoleToPublish : null,
    };
  },
  computed: {
    searchUnavailable() {
      return (!this.origin || !this.destination || (!this.dataRegular && !this.date && !this.punctualDateOptional))
    },
    dateFormated() {
      return this.date
        ? moment.utc(this.date).format(this.$t("urlDate"))
        : null;
    },
    timeFormated() {
      return this.time
        ? moment.utc(this.time).format(this.$t("urlTime"))
        : null;
    }
  },
  watch:{
    defaultOrigin(newValue){
      this.origin = newValue;
    }

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
      this.time = search.time;
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
      let lParams = {
        origin: JSON.stringify(this.origin),
        destination: JSON.stringify(this.destination),
        regular:this.dataRegular,
        date:this.date?this.date:null,
        time:this.time?this.time:null,
        ...this.params
      };
      if (this.logged){
        this.loadingPublish = true;
        this.post(`${this.$t("buttons.publish.route")}`, lParams);
      }else{
        if (this.params && this.params.eventId){
          localStorage.setItem('adSettings', JSON.stringify(lParams));
        }
        window.location.href=this.$t("buttons.shareAnAd.route");
      }
    },
    determineOrigin: function(address){
      if(this.defaultOrigin){
        return this.defaultOrigin;
      }

      if(this.user && this.user.homeAddress && this.user.homeAddress.latitude && this.user.homeAddress.longitude){
        return this.user.homeAddress
      }

      return null;
    }
  },
};
</script>
