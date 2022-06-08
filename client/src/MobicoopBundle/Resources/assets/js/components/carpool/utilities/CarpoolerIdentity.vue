<template>
  <div>
    <v-list-item
      class="pa-0"
      @click="showProfileDialog = true"
    >
      <!--Carpooler avatar-->
      <ProfileAvatar
        :avatar="carpooler.avatars[1]"
        :experienced="carpooler.experienced"
        :minimized="true"
      />
      <!--Carpooler data-->
      <v-list-item-content>
        <v-list-item-title class="font-weight-bold">
          {{ carpooler.givenName }} {{ carpooler.shortFamilyName }}
        </v-list-item-title>

        <v-list-item-title v-if="ageDisplay">
          {{ age }}
        </v-list-item-title>
      </v-list-item-content>
      <v-img
        v-if="hasBadges && gamificationActive"
        src="/images/badge.png"
        contain
        max-width="25"
      >
        <p class="caption text-center mt-1 primary--text font-weight-bold">
          {{ carpooler.numberOfBadges }}
        </p>
      </v-img>
    </v-list-item>
    <PopupPublicProfile
      :carpooler="carpooler"
      :show-profile-dialog="showProfileDialog"
      @dialogClosed="showProfileDialog = false"
    />
  </div>
</template>

<script>
import moment from "moment";
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
import PopupPublicProfile from "@components/user/profile/PopupPublicProfile";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/carpool/utilities/CarpoolerSummary/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  components:{
    ProfileAvatar,
    PopupPublicProfile
  },
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    ageDisplay: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      hasBadges: this.carpooler.numberOfBadges>0 ? true : false,
      showProfileDialog: false
    }
  },
  computed: {
    age (){
      if (this.carpooler.birthYear) {
        return moment().diff(moment([this.carpooler.birthYear]),'years') + ' ' + this.$t("birthYears");
      } else {
        return null;
      }
    },
    gamificationActive(){
      return this.$store.getters['g/isActive'];
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  }
}
</script>

<style scoped>

</style>
