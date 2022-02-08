<template>
  <v-dialog
    v-model="dialog"
    width="80%"
    @click:outside="close"
  >
    <v-card>
      <v-card-title class="headline grey lighten-2">
        <v-row dense>
          <v-col>
            {{ $t('title') }}
          </v-col>
          <v-col class="text-right">
            <v-btn
              class="text"
              icon
              @click="close"
            >
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-card-title>

      <v-card-text>
        <v-row>
          <v-col>{{ $t('privateLife') }} : <strong>{{ $t('yourBrowseryourChoice', {appName:appName}) }}</strong></v-col>
        </v-row>
        <v-row>
          <v-col cols="4">
            {{ $t('working.line1') }} : <br>{{ $t('working.line2') }}.
          </v-col>
          <v-col cols="6">
            <v-slider
              v-model="level"
              :disabled="!progressBarActive"
              step="1"
              ticks="always"
              :tick-size="socialNetworksActive ? 2 : 1"
              :max="socialNetworksActive ? 2 : 1"
              @change="updatePrefs"
            />
          </v-col>
          <v-col cols="2">
            <v-btn
              color="secondary"
              :disabled="!recordValid"
              @click="store"
            >
              {{ $t('store') }}
            </v-btn>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="4">
            {{ $t('privacyPolicy.text') }}
            <a
              target="_blank"
              :href="$t('privacyPolicy.link.uri')"
              :title="$t('privacyPolicy.link.label')"
            >{{ $t('privacyPolicy.link.label') }}</a>.
          </v-col>
          <v-col
            cols="2"
          >
            <v-checkbox
              v-model="checkboxes.connectionActive"
              :disabled="checkboxes.connectionActiveDisabled"
              class="ma-0 subtitle-2"
              :label="$t('checkboxes.connectionActive.desc')"
              @click="disableProgressBar()"
            />
            <span class="font-italic subtitle-2"><strong>{{ $t('checkboxes.connectionActive.mandatoryOrNot') }}</strong></span>
          </v-col>
          <v-col
            cols="3"
          >
            <v-checkbox
              v-model="checkboxes.stats"
              class="ma-0 subtitle-2"
              :label="$t('checkboxes.stats.desc')"
              @click="disableProgressBar()"
            />
            <span class="font-italic subtitle-2">{{ $t('checkboxes.stats.mandatoryOrNot') }}</span>
            <p class="mt-2 subtitle-2">
              {{ $t('checkboxes.stats.details') }}.
            </p>
          </v-col>
          <v-col
            v-if="socialCookies.length == 0 && (socialNetworksActive || forceShowSocial)"
            cols="3"
          >
            <v-checkbox
              v-model="checkboxes.social"
              class="ma-0 subtitle-2"
              :label="$t('checkboxes.social.desc')"
              @click="disableProgressBar()"
            />
            <span class="font-italic subtitle-2">{{ $t('checkboxes.social.mandatoryOrNot') }}</span>
          </v-col>
          <v-col v-else-if="socialNetworksActive || forceShowSocial">
            <p class="text-subtitle-1">
              {{ $t('checkboxes.social.several.title') }}
            </p>
            <ul>
              <li
                v-for="(socialCookie,i) in socialCookies"
                :key="i"
                style="list-style-type: none"
              >
                <v-checkbox
                  v-model="checkboxes.socialCookies"
                  class="ma-0"
                  :label="socialCookie"
                  :value="socialCookie"
                  @click="disableProgressBar()"
                />
              </li>
            </ul>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
 
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/Cookies/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/Cookies/";

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
      'eu':MessagesMergedEu
    }
  },
  components: { 
    
  },
  props:{
    appName:{
      type: String,
      default:null
    },
    show:{
      type: Boolean,
      default: false
    },
    autoShow:{
      type: Boolean,
      default: true
    },
    forceShowSocial:{
      type: Boolean,
      default: false
    },
    socialNetworksActive:{
      type: Boolean,
      default: false
    },
    connectionActiveCheckDefault:{
      type: Boolean,
      default: false
    },
    statsCheckDefault:{
      type: Boolean,
      default: false
    },
    socialCheckDefault:{
      type: Boolean,
      default: false
    },
    socialCookies:{
      type: Array,
      default: null
    }
  },
  data(){
    return{
      dialog:this.show,
      level:0,
      progressBarActive:true,
      defaultSettings:null,
      checkboxes:{
        connectionActive:this.connectionActiveCheckDefault,
        connectionActiveDisabled:this.connectionActiveCheckDefault ? true : false,
        stats:this.statsCheckDefault,
        social:this.socialCheckDefault,
        socialCookies:[]
      }
    }
  },
  computed:{
    recordValid(){
      if(this.checkboxes.connectionActive){
        return true;
      }
      return false;
    }
  },
  watch:{
    show(){
      this.dialog = this.show;
    },
    'checkboxes.socialCookies': function() {
      (this.checkboxes.socialCookies.length>0) ? this.checkboxes.social = true : this.checkboxes.social = false
    }    
  },
  mounted(){
    this.getDefault();
    if(this.socialCookies.length > 0){
      this.disableProgressBar();
    }
  },
  methods:{
    updatePrefs(){
      switch(this.level){
      case 0:
        this.checkboxes.connectionActive = false;
        this.checkboxes.stats = false;
        this.checkboxes.social = false;
        break;
      case 1:
        this.checkboxes.connectionActive = true;
        this.checkboxes.stats = true;
        this.checkboxes.social = false;
        break;
      default:
        this.checkboxes.connectionActive = true;
        this.checkboxes.stats = true;
        this.checkboxes.social = true;
        break;
      }
    },
    disableProgressBar(){
      this.progressBarActive = false;
    },
    getDefault(){
      // Get the default settings in local storage in exists
      this.defaultSettings = JSON.parse(localStorage.getItem("cookies_prefs"));
      if(this.defaultSettings){
        this.checkboxes.connectionActive = this.defaultSettings.connectionActive;
        if(!this.autoShow) this.checkboxes.connectionActiveDisabled = true;
        this.checkboxes.stats = this.defaultSettings.stats;
        this.checkboxes.socialCookies = this.defaultSettings.socialCookies;
        this.checkboxes.social = (this.checkboxes.socialCookies.length > 0) || this.defaultSettings.social;
        this.store();
        this.disableProgressBar();
      }
      else{
        // If no data in local storage and autoShow is true, we need to show the popup
        if(this.autoShow){
          this.dialog = true;
        }
        else{
          // Autoshow is inactive, we set somes choices to true and disable the slide barre
          this.checkboxes.connectionActive = true;
          this.checkboxes.connectionActiveDisabled = true;
          this.checkboxes.stats = true;
          this.store();
          this.disableProgressBar();
        }
      }
    },
    store(){
      // Store settings in local storage and vuex store
      let prefs = {
        connectionActive:this.checkboxes.connectionActive,
        stats:this.checkboxes.stats,
        socialCookies:this.checkboxes.socialCookies,
        social:this.checkboxes.social
      }
      localStorage.setItem('cookies_prefs',JSON.stringify(prefs));
      this.$store.commit('up/updateConnectionActive',prefs.connectionActive);
      this.$store.commit('up/updateSocial',prefs.social);
      this.$store.commit('up/updateSocialCookies',prefs.socialCookies);
      this.$store.commit('up/updateStats',prefs.stats);

      this.close();
    },
    close(){
      this.dialog = false;
      this.$emit("dialogClosed");
    }
  }
}
</script>