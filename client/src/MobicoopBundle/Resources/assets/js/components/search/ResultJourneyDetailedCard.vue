<template>
  <v-content>
    <v-container fluid>
      <v-row>
        <!-- avatar --> 
        <v-col
          justify-space-around
        >
          <v-avatar
            color="primary"
          />
        </v-col>

        <!-- hour -->
        <v-col
          class="text-address-size"
        >
          <v-row
            top
          >
            <h3>09h00</h3>
          </v-row>

          <!-- date -->
          <v-row>  
            <p>
              {{ displaydate(date) }}
            </p>
          </v-row>  
        </v-col>

        <!-- origin -->
        <v-col
          class="text-left text-address-size"
        >
          <v-row>
            {{ origin }}
          </v-row>
        </v-col> 

        <!-- icon --> 
        <v-col>
          <v-icon
            :color="secondary"
            size="32"
          >
            mdi-ray-start
          </v-icon>  
          <v-icon
            :color="secondary"
            size="32"
          >
            mdi-ray-end
          </v-icon>
        </v-col> 

        <!-- destination --> 
        <v-col 
          class="text-left text-address-size"
        >
          <v-row>
            {{ destination }}
          </v-row>
        </v-col>

        <!--seats --> 
        <v-col>
          <p>3 places</p>
        </v-col>

        <!-- price --> 
        <v-col>
          <h3>50€</h3>
        </v-col>
      </v-row>

      <!--     <v-btn
        color="primary"
        fab
        large
        depressed
        @click="driver"
      >
        <v-icon>mdi-account-supervisor</v-icon><br>
      </v-btn>

      <v-btn
        color="secondary"
        fab
        large
        depressed
        @click="passenger"
      >
        <v-icon>mdi-steering</v-icon><br>
      </v-btn> -->
    </v-container>
  </v-content>
</template>
<script>
import moment from "moment";

export default {
  name: "ResultJourneyDetailedCard",
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
    matchingSearchUrl: {
      type: String,
      default: null
    },
  },
  data: function() {
    return {
      driver : null,
      passenger : null
    };
  },
  methods : {
    displaydate(date){
      return moment (new Date(date)).utcOffset("+00:00").format('ddd DD/MM ')
    },
  }
}
</script>

  <style scoped>
  .text-address-size {
  font-size: 17px;
  }
  </style>