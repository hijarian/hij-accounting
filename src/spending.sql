create table if not exists spending (
  id integer PRIMARY KEY,
  date text,
  place int,
  name text,
  amount float not null,
  unit int not null,
  price int not null,
  discount int not null,
  tags text not null
);

create table if not exists units (
  id integer primary key,
  name text not null
);

insert into units (name) values ('шт');
insert into units (name) values ('кг');
insert into units (name) values ('л');
insert into units (name) values ('м2');

create table if not exists places (
  id integer primary key,
  name text not null
);