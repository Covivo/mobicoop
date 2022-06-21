<template>
  <l-marker
    v-if="!circleMarker"
    :lat-lng="point.latLng"
    :draggable="markersDraggable"
    @update:latLng="updateLatLng"
    @click="clickOnPoint(point.address)"
  >
    <l-icon
      v-if="point.icon.url!==undefined && !point.circleMarker"
      :icon-size="point.icon.size"
      :icon-anchor="point.icon.anchor"
      :icon-url="point.icon.url"
    />


    <l-tooltip
      v-if="point.title!==''"
    >
      <p
        class="font-weight-bold"
        v-html="point.title"
      />

      <MMapRelayPointDescription
        v-if="relayPoints && point.misc"
        :data="point.misc"
      />
    </l-tooltip>
    <l-popup
      v-if="point.popup"
      class="popup"
    >
      <h3 v-html="point.popup.title" />
      <img
        v-if="point.popup.images && point.popup.images[0]"
        :src="point.popup.images[0]['versions']['square_100']"
        alt="avatar"
      >
      <p
        v-html="point.popup.description"
      />
      <p v-if="point.popup.date_begin && point.popup.date_end">
        {{ point.popup.date_begin }}<br> {{ point.popup.date_end }}
      </p>
      <p v-if="point.popup.linktoevent && point.popup.linktoevent">
        >
        <a
          :href="point.popup.linktoevent"
        >{{ $t('seeEvent') }}</a>
      </p>
    </l-popup>
  </l-marker>
  <l-circle-marker
    v-else
    :lat-lng="point.latLng"
    :color="color"
    :fill-color="color"
    :fill-opacity="0.5"
  />
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/event/EventList/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/event/EventList/";
import MMapRelayPointDescription from "@components/utilities/MMap/MMapRelayPointDescription"

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
    MMapRelayPointDescription
  },
  props: {
    point: {
      type: Object,
      default: function(){return [];}
    },
    relayPoints: {
      type: Boolean,
      default: false
    },
    markersDraggable: {
      type: Boolean,
      default: false
    },
    circleMarker: {
      type: Boolean,
      default: false
    },
    color:{
      type: String,
      default: ""
    }
  },
  methods:{
    updateLatLng(data){
      this.$emit("updateLatLng",data);
    },
    clickOnPoint(point){
      this.$emit("clickOnPoint",point);
    }
  }
}
</script>
<style lang="scss" scoped>
.popup{
  overflow: scroll;
  overflow-x: auto;
    overflow-y: auto;

  max-height: 400px;
  }
.tooltip{
  overflow: hidden;
  max-width: 300px;
  white-space: nowrap;
  text-overflow: ellipsis;

  }
</style>
