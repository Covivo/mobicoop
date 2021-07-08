<template>
  <div class="mt-5 black--text">
    <div v-if="loading">
      <v-skeleton-loader
        class="mx-auto"
        type="card"
      />
    </div>
    <div v-else-if="profileSummary">
      <!-- Avatar -->
      <ProfileAvatar
        :avatar="profileSummary.avatar"
        :experienced="profileSummary.experienced"
      />
      <div
        v-if="showLinkProfile"
        class="text-center"
      >
        <a
          href="#"
          title=""
          @click="showProfile"
        >Voir le profil</a>
      </div>
      <div>
        <v-row
          no-gutters
          class="title"
        >
          <v-col
            cols="12"
            class="text-center"
          >
            {{ profileSummary.givenName }} {{ profileSummary.shortFamilyName }}
          </v-col>
        </v-row>
        <v-row
          v-if="profileSummary.age"
          no-gutters
        >
          <v-col
            cols="12"
            class="text-center body-2"
          >
            <span v-if="ageDisplay">
              {{ profileSummary.age }} {{ $t('yearsOld') }}<br>
            </span>
            <span v-if="profileSummary && profileSummary.phoneDisplay == 2">{{ profileSummary.telephone }}</span>
          </v-col>
        </v-row>
      </div>
      <div class="body-2 pa-2">
        <v-row
          dense
        >
          <v-col
            cols="12"
            class="text-left"
          >
            <v-row dense>
              <v-col>
                <v-icon>mdi-car</v-icon> {{ $t('infos.carpoolRealized') }} : {{ profileSummary.carpoolRealized }}
              </v-col>
            </v-row>
            <v-row dense>
              <v-col>
                <v-icon>mdi-chat-processing</v-icon> {{ $t('infos.answerPct') }} : {{ profileSummary.answerPct }}%
              </v-col>
            </v-row>
            <v-row
              v-if="lastConnection"
              dense
            >
              <v-col
                cols="1"
                class="mr-2"
              >
                <v-icon>mdi-account-clock</v-icon>
              </v-col>
              <v-col>
                <v-row dense>
                  <v-col>
                    {{ $t('infos.lastConnection') }} :
                  </v-col>
                </v-row>
                <v-row dense>
                  <v-col>
                    {{ lastConnection }}
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </div>
    </div>
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import moment from "moment";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/ProfileSummary/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components:{
    ProfileAvatar
  },
  props:{
    userId:{
      type:Number,
      default: null
    },
    showLinkProfile:{
      type: Boolean,
      default: true
    },
    refresh:{
      type: Boolean,
      default: false
    },
    ageDisplay: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return{
      profileSummary:null,
      loading:true,
      experienced:false
    }
  },
  computed:{
    lastConnection(){
      if (this.profileSummary.lastActivityDate) {
        return moment(this.profileSummary.lastActivityDate.date).format('DD/MM/YYYY');
      }
      return "-";
      
    },
  },
  watch:{
    refresh(){
      if(this.refresh){
        this.getProfileSummary();
      }
    }
  },
  mounted(){
    this.getProfileSummary()
  },
  methods:{
    getProfileSummary(){
      this.loading = true;
      maxios.post(this.$t('getProfileSummaryUri'),{'userId':this.userId})
        .then(response => {
          //console.log(response.data);
          this.profileSummary = response.data;
          this.loading = false;
          this.$emit('profileSummaryRefresh',{'userId':this.userId});
        })
        .catch(function (error) {
          console.error(error);
        });
    },
    showProfile(){
      this.$emit("showProfile",{'userId':this.userId});
    }
  }
}
</script>