import React from 'react';

const addressRenderer = address => (address && address.displayLabel && address.displayLabel.length) ? `${address.displayLabel[0]} - ${address.displayLabel[1]}` : ""

const UserRenderer = ({record}) => <span>{record.givenName} {record.familyName} </span>

export {addressRenderer, UserRenderer }
