const fs = require('fs');
const path = require('path');

const rootDir = path.join(__dirname, '..');
const sourceDir = path.join(rootDir, 'src');
const pluginSlug = 'openagenda-events-calendar';
const pluginFile = path.join(sourceDir, `${pluginSlug}.php`);

function readVersion() {
  const content = fs.readFileSync(pluginFile, 'utf8');
  const match = content.match(/define\(\s*'OPENAGENDA_VERSION'\s*,\s*'([^']+)'\s*\)/);

  if (!match) {
    throw new Error(`Could not read OPENAGENDA_VERSION from ${pluginSlug}.php.`);
  }

  return match[1];
}

function createCrcTable() {
  const table = new Uint32Array(256);

  for (let i = 0; i < 256; i++) {
    let value = i;

    for (let bit = 0; bit < 8; bit++) {
      value = (value & 1) ? (0xedb88320 ^ (value >>> 1)) : (value >>> 1);
    }

    table[i] = value >>> 0;
  }

  return table;
}

const crcTable = createCrcTable();

function crc32(buffer) {
  let crc = 0xffffffff;

  for (const byte of buffer) {
    crc = crcTable[(crc ^ byte) & 0xff] ^ (crc >>> 8);
  }

  return (crc ^ 0xffffffff) >>> 0;
}

function dosDateTime(date) {
  const year = Math.max(1980, date.getFullYear());

  return {
    date: ((year - 1980) << 9) | ((date.getMonth() + 1) << 5) | date.getDate(),
    time: (date.getHours() << 11) | (date.getMinutes() << 5) | Math.floor(date.getSeconds() / 2),
  };
}

function writeUInt16(value) {
  const buffer = Buffer.alloc(2);
  buffer.writeUInt16LE(value, 0);
  return buffer;
}

function writeUInt32(value) {
  const buffer = Buffer.alloc(4);
  buffer.writeUInt32LE(value >>> 0, 0);
  return buffer;
}

function collectFiles(directory, prefix = '') {
  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const absolutePath = path.join(directory, entry.name);
    const relativePath = prefix ? `${prefix}/${entry.name}` : entry.name;

    if (entry.isDirectory()) {
      return collectFiles(absolutePath, relativePath);
    }

    if (!entry.isFile()) {
      return [];
    }

    return [{ absolutePath, relativePath: `${pluginSlug}/${relativePath}` }];
  });
}

function createZip(source, destination) {
  const localParts = [];
  const centralParts = [];
  let offset = 0;

  collectFiles(source).forEach(({ absolutePath, relativePath }) => {
    const data = fs.readFileSync(absolutePath);
    const name = Buffer.from(relativePath, 'utf8');
    const stats = fs.statSync(absolutePath);
    const timestamp = dosDateTime(stats.mtime);
    const checksum = crc32(data);

    const localHeader = Buffer.concat([
      writeUInt32(0x04034b50),
      writeUInt16(20),
      writeUInt16(0x0800),
      writeUInt16(0),
      writeUInt16(timestamp.time),
      writeUInt16(timestamp.date),
      writeUInt32(checksum),
      writeUInt32(data.length),
      writeUInt32(data.length),
      writeUInt16(name.length),
      writeUInt16(0),
      name,
    ]);

    localParts.push(localHeader, data);

    centralParts.push(Buffer.concat([
      writeUInt32(0x02014b50),
      writeUInt16(20),
      writeUInt16(20),
      writeUInt16(0x0800),
      writeUInt16(0),
      writeUInt16(timestamp.time),
      writeUInt16(timestamp.date),
      writeUInt32(checksum),
      writeUInt32(data.length),
      writeUInt32(data.length),
      writeUInt16(name.length),
      writeUInt16(0),
      writeUInt16(0),
      writeUInt16(0),
      writeUInt16(0),
      writeUInt32(0),
      writeUInt32(offset),
      name,
    ]));

    offset += localHeader.length + data.length;
  });

  const centralDirectory = Buffer.concat(centralParts);
  const endOfCentralDirectory = Buffer.concat([
    writeUInt32(0x06054b50),
    writeUInt16(0),
    writeUInt16(0),
    writeUInt16(centralParts.length),
    writeUInt16(centralParts.length),
    writeUInt32(centralDirectory.length),
    writeUInt32(offset),
    writeUInt16(0),
  ]);

  fs.writeFileSync(destination, Buffer.concat([...localParts, centralDirectory, endOfCentralDirectory]));
}

const version = readVersion();
let zipPath = path.join(rootDir, `${pluginSlug}-${version}.zip`);

function prepareZipPath(target) {
  if (!fs.existsSync(target)) {
    return target;
  }

  try {
    fs.rmSync(target, { force: true, maxRetries: 5, retryDelay: 250 });
    return target;
  } catch (error) {
    const fallback = path.join(rootDir, `${pluginSlug}-${version}-fixed.zip`);

    if (!fs.existsSync(fallback)) {
      return fallback;
    }

    return path.join(rootDir, `${pluginSlug}-${version}-${Date.now()}.zip`);
  }
}

zipPath = prepareZipPath(zipPath);
createZip(sourceDir, zipPath);

console.log(`Wrote ${zipPath}`);
