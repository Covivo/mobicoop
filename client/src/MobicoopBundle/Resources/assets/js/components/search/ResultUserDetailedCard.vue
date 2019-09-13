<template>
  <v-content>
    <v-container 
      fluid
    >
      <v-list-item>
        <!--user avatar-->
        <v-list-item-avatar
          color="grey darken-3"
          size="80"
        >
          <v-img
            aspect-ratio="2"
            src="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortRound&accessoriesType=Blank&hairColor=BrownDark&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Default&skinColor=Light"
          />
        </v-list-item-avatar>
        <!--user data-->
        <v-list-item-content>
          <v-list-item-title class="font-weight-bold">
            {{ matching.user.givenName }} {{ matching.user.familyName.substr(0,1).toUpperCase()+"." }}
          </v-list-item-title>
          <v-list-item-title>{{ formatedYear(matching.user.birthDate) }} ans </v-list-item-title>
          <v-list-item-title
            class="caption font-weight-light font-italic"
          >
            Annonce ouestgo.fr
          </v-list-item-title>
        </v-list-item-content>
        <!--user stars-->
        <v-row
          align="center"
          justify="start"
        >
          <span class="yellow--text text--darken-2">4.7
          </span>
          <v-icon
            :color="'yellow darken-2'"
            class="ml-1"
          >
            mdi-star
          </v-icon>
        </v-row>
        <!--user phone and mail-->
        <v-row
          align="center"
          justify="end"
          class="min-width-no-flex"
        >
          <v-btn
            color="success"
            small
            dark
            depressed
            rounded
            :hidden="!togglePhoneButton"
            height="40px"
            @click="toggleButton"
          >
            <v-icon>mdi-phone</v-icon>
            <div
              class="ml-2"
            >
              {{ matching.user.telephone }}
            </div>
          </v-btn>
          <v-btn
            color="success"
            small
            dark
            depressed
            fab
            :hidden="togglePhoneButton"
            @click="toggleButton"
          >
            <v-icon>mdi-phone</v-icon>
          </v-btn>

          <v-btn
            color="success"
            small
            dark
            depressed
            fab
            class="ml-2"
          >
            <v-icon>mdi-email</v-icon>
          </v-btn>
        </v-row>
        <!--        book button-->
        <v-row
          align="center"
          justify="end"
        >
          <v-btn
            rounded
            color="success"
            large
            dark
          >
            <span>
              Covoiturer
            </span>
          </v-btn>
        </v-row>
      </v-list-item>
    </v-container>
  </v-content>
</template>

<script>
import moment from "moment";
import 'moment/locale/fr';
export default {
  name: "ResultUserDetailedCard",
  props: {
    origin: {
      type: String,
      default: null
    },
    destination: {
      type: String,
      default: null
    },
    originLatitude: {
      type: String,
      default: null
    },
    originLongitude: {
      type: String,
      default: null
    },
    destinationLatitude: {
      type: String,
      default: null
    },
    destinationLongitude: {
      type: String,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    carpoolResults: {
      type: Object,
      default: null
    },
    matchingSearchUrl: {
      type: String,
      default: null
    },
    matching: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      togglePhoneButton: false,
    };
  },
  methods: {
    toggleButton: function(){
      this.togglePhoneButton = !this.togglePhoneButton;
    },
    formatedYear (){
      return moment().diff(moment(this.matching.user.birthDate),'years')
    } 
  }
}
</script>

<style scoped>

  .min-width-no-flex{
    min-width: 250px;
    flex: none;
  }
</style>