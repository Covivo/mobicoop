<template>
  <v-container fluid>
    <v-row v-if="loading">
      <v-col cols="12">
        <v-skeleton-loader
          class="mx-auto"
          type="card"
        />        
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-row>
          <v-col cols="4">
            <v-row>
              <v-col cols="8">
                <ProfileAvatar
                  :avatar="publicProfile.avatar"
                  :experienced="publicProfile.experienced"
                />
              </v-col>
              <v-col
                cols="4"
                class="text-right"
              >
                <v-row>
                  <v-col>
                    {{ publicProfile.givenName }} {{ publicProfile.shortFamilyName }}<br>
                    <span v-if="ageDisplay && publicProfile.age">
                      {{ publicProfile.age }} {{ $t('yearsOld') }}
                    </span>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col>CO<sup>2</sup> {{ $t('savedCo2', {savedCo2:savedCo2}) }}</v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-col>
          <v-col
            cols="3"
            class="text-center"
          >
            <p>{{ $t('carpoolRealized') }}<br><span class="headline">{{ publicProfile.carpoolRealized }}</span></p>
            <p v-if="lastConnection">
              {{ $t('lastConnection') }}<br>{{ lastConnection }}
            </p>
          </v-col>
          <v-col
            cols="3"
            class="text-center"
          >
            <p>
              {{ $t('answerRate') }}<br>
              <v-progress-linear
                :color="answerRateColor"
                height="25"
                :value="publicProfile.answerPct"
              >
                <template v-slot:default="{ value }">
                  <strong>{{ Math.ceil(value) }}%</strong>
                </template>
              </v-progress-linear>
            </p>
            <p>{{ $t('subscribedOn') }}<br>{{ subscribedOn }}</p>
          </v-col>
          <v-col
            cols="2"
            class="text-center"
          >
            <v-row>
              <v-col>
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <div
                      v-bind="attrs"
                      v-on="on"
                    >
                      <v-icon>{{ smokingIcon }}</v-icon><v-icon v-if="smokingCarIcon">
                        {{ smokingCarIcon }}
                      </v-icon>
                    </div>
                  </template>
                  <span>{{ smokingIconToolTip }}</span>
                </v-tooltip>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon
                      v-bind="attrs"
                      v-on="on"
                    >
                      {{ chatIcon }}
                    </v-icon>
                  </template>
                  <span>{{ chatIconToolTip }}</span>
                </v-tooltip>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon
                      v-bind="attrs"
                      v-on="on"
                    >
                      {{ musicIcon }}
                    </v-icon>
                  </template>
                  <span>{{ musicIconToolTip }}</span>
                </v-tooltip>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
        <v-row v-if="showReportButton">
          <v-col class="text-right">
            <Report
              :user="user"
            />
          </v-col>
        </v-row> 
        <v-row>
          <v-col
            cols="8"
            sm="12"
          >
            <v-row v-if="publicProfile && publicProfile.reviewActive && publicProfile.reviews.length > 0">
              <v-col cols="12">
                <Reviews :reviews="publicProfile.reviews" />
              </v-col>
            </v-row>
          </v-col>
          <v-col
            cols="4"
            sm="12"
          >
            <v-row v-if="publicProfile && publicProfile.badges.length > 0">
              <v-col cols="12">
                <Badges :badges="publicProfile.badges" />
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";
import moment from "moment";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
import Reviews from "@components/utilities/Reviews/Reviews";
import Badges from "@components/utilities/gamification/Badges";
import Report from "@components/utilities/Report";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/PublicProfile/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    ProfileAvatar,
    Report,
    Reviews,
    Badges
  },
  props:{
    user:{
      type:Object,
      default: null
    },
    showReportButton: {
      type: Boolean,
      default: true
    },
    refresh:{
      type: Boolean,
      default: false
    },
    ageDisplay: {
      type: Boolean,
      default: true
    }
  },
  data(){
    return{
      publicProfile:null,
      loading:true,
      locale: localStorage.getItem("X-LOCALE")
    }
  },
  computed:{
    lastConnection(){
      if (this.publicProfile.lastActivityDate) {
        return moment(this.publicProfile.lastActivityDate.date).format('DD/MM/YYYY');
      }
      return null;
      
    },
    subscribedOn(){
      return moment(this.publicProfile.createdDate.date).format('DD/MM/YYYY');
    },
    answerRateColor(){
      if(this.publicProfile.answerPct < 33){
        return 'error'
      }
      else if(this.publicProfile.answerPct < 66){
        return 'warning'
      }
      else{
        return 'success'
      }
    },
    musicIcon(){
      return (this.publicProfile.music) ? 'mdi-music' : 'mdi-music-off';
    },
    musicIconToolTip(){
      return (this.publicProfile.chat) ? this.$t('params.music') : this.$t('params.noMusic');
    },    
    chatIcon(){
      return (this.publicProfile.chat) ? 'mdi-account-voice' : 'mdi-voice-off';
    },
    chatIconToolTip(){
      return (this.publicProfile.chat) ? this.$t('params.chat') : this.$t('params.noChat');
    },
    smokingIcon(){
      switch(this.publicProfile.smoke){
      case 0:
      case 1: return 'mdi-smoking-off';
      case 2: return 'mdi-smoking';
      }
      return 'mdi-smoking-off';
    },
    smokingCarIcon(){
      return (this.publicProfile.smoke==1) ? 'mdi-car' : '';
    },
    smokingIconToolTip(){
      switch(this.publicProfile.smoke){
      case 0: return this.$t('params.noSmoke');
      case 1: return this.$t('params.noSmokeInCar');
      case 2: return this.$t('params.smoke');
      }
      return this.$t('params.noSmoke');
    },
    savedCo2(){
      return Number.parseFloat(this.publicProfile.savedCo2  / 1000000 ).toPrecision(1);
    }
  },
  watch:{
    refresh(){
      if(this.refresh){
        this.getPublicProfile();
      }
    }
  },
  mounted(){
    moment.locale(this.locale)
    this.getPublicProfile()
  },
  methods:{
    getPublicProfile(){
      maxios.post(this.$t('getPublicProfileUri'),{'userId':this.user.id})
        .then(response => {
          //console.log(response.data);
          this.publicProfile = response.data;
          this.loading = false;
          this.$emit('publicProfileRefresh',{'user':this.user});
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }
}
</script>