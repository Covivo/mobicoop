import * as React from 'react';
import { FC } from 'react';
import { Card, CardHeader, CardContent } from '@material-ui/core';
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip } from 'recharts';
import { useTranslate } from 'react-admin';

const lastDay = new Date(new Date().toDateString()).getTime();
const oneDay = 24 * 60 * 60 * 1000;
const lastMonthDays = Array.from({ length: 30 }, (_, i) => lastDay - i * oneDay).reverse();

const aMonthAgo = new Date();

aMonthAgo.setDate(aMonthAgo.getDate() - 30);

const dateFormatter = (date) => new Date(date).toLocaleDateString();

const aggregateByDay = (users) =>
  users
    .filter((user) => !user.unsubscribeDate)
    .reduce((acc, curr) => {
      const day = new Date(new Date(curr.createdDate.slice(0, 10)).toDateString()).getTime();
      if (!acc[day]) {
        acc[day] = 0;
      }
      acc[day] += 1;
      return acc;
    }, {});

const getUsersByDay = (users) => {
  const daysWithUsers = aggregateByDay(users);
  return lastMonthDays.map((date) => ({
    date,
    total: daysWithUsers[date] || 0,
  }));
};

const UsersChart = ({ users }) => {
  const translate = useTranslate();
  if (!users || !users.length) return null;

  return (
    <Card>
      <CardHeader title="Historique des dernières inscriptions" />
      <CardContent>
        <ResponsiveContainer width="100%" height={300}>
          <BarChart data={getUsersByDay(users)}>
            <XAxis
              dataKey="date"
              name="Date"
              type="number"
              scale="time"
              domain={[aMonthAgo.getTime(), new Date().getTime()]}
              tickFormatter={dateFormatter}
            />
            <YAxis dataKey="total" name="Nb" />
            <CartesianGrid strokeDasharray="3 3" />

            <Bar dataKey="total" fill="#31708f" />
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
};

export default UsersChart;
