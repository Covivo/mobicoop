<template>
  <v-dialog
    v-model="dialog"
    width="80%"
  >
    <v-card>
      <v-card-title class="headline grey lighten-2">
        {{ $t('title') }}
      </v-card-title>

      <v-card-text>
        <v-row>
          <v-col>{{ $t('privateLife') }} : <strong>{{ $t('yourBrowseryourChoice') }}</strong></v-col>
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
              tick-size="2"
              max="2"
              @change="updatePrefs"
            />
          </v-col>
          <v-col cols="2">
            <v-btn
              color="secondary"
              :disabled="!recordValid"
            >
              {{ $t('record') }}
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
            class="caption"
          >
            <v-checkbox
              v-model="checkboxes.connectionActive"
              :label="$t('checkboxes.connectionActive.desc')"
              @click="disableProgressBar()"
            />
          </v-col>
          <v-col
            cols="4"
            class="caption"
          >
            <v-checkbox
              v-model="checkboxes.stats"
              :label="$t('checkboxes.stats.desc')"
              @click="disableProgressBar()"
            />
          </v-col>
          <v-col
            cols="2"
            class="caption"
          >
            <v-checkbox
              v-model="checkboxes.social"
              :label="$t('checkboxes.social.desc')"
              @click="disableProgressBar()"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
 
<script>
//import CookieLaw from 'vue-cookie-law';
import {messages_en, messages_fr} from "@translations/components/utilities/Cookies/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  components: { 
    
  },
  props:{
    show:{
      type: Boolean,
      default: false
    }
  },
  data(){
    return{
      dialog:this.show,
      level:0,
      progressBarActive:true,
      checkboxes:{
        connectionActive:false,
        stats:false,
        social:false
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
    }
  }
}
</script>